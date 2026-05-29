<?php
include("conexion.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0"/>

<title>Viene La Micro</title>

<script src="https://cdn.tailwindcss.com"></script>

<style>

html{
    scroll-behavior:smooth;
}

body{
    background:#0b0b0b;
    overflow-x:hidden;
}

</style>

</head>

<body class="text-white font-sans">

<!-- HEADER -->
<header class="absolute top-0 left-0 w-full z-50 border-b border-white/10">

    <div class="max-w-[1400px] mx-auto px-8 py-6 flex items-center justify-between">

        <div class="text-3xl tracking-[0.35em] font-light">
            VIENE LA MICRO
        </div>

        <nav class="hidden lg:flex items-center gap-10 uppercase text-[12px] tracking-[0.25em] text-gray-300">

            <a href="#" class="hover:text-white transition">
                Inicio
            </a>

            <a href="#recorridos" class="hover:text-white transition">
                Recorridos
            </a>

            <a href="#" class="hover:text-white transition">
                Horarios
            </a>

            <a href="mapa.html" class="hover:text-white transition">
                Mapa
            </a>

        </nav>

        <button class="border border-[#00d4ff] text-[#00d4ff] px-7 py-3 rounded-full uppercase tracking-[0.2em] text-xs hover:bg-[#00d4ff] hover:text-black transition duration-300">

            Ver Buses

        </button>

    </div>

</header>

<!-- HERO -->
<section class="relative min-h-screen flex items-center justify-center">

    <img
        src="https://images.unsplash.com/photo-1570125909232-eb263c188f7e?q=80&w=2000&auto=format&fit=crop"
        class="absolute inset-0 w-full h-full object-cover"
    />

    <div class="absolute inset-0 bg-black/75"></div>

    <div class="relative z-10 text-center px-6 max-w-6xl pt-32">

        <p class="uppercase tracking-[0.6em] text-[#00d4ff] text-sm mb-8">

            Sistema Inteligente de Transporte

        </p>

        <h1 class="text-6xl md:text-8xl xl:text-[120px] leading-none font-light tracking-tight">

            Monitorea
            <br>
            Tu Bus

        </h1>

        <div class="mt-10 text-gray-300 text-lg max-w-2xl mx-auto leading-relaxed">

            Consulta recorridos, horarios y ubicación en tiempo real
            de buses de Coihueco, Cato y Chillán.

        </div>

        <div class="mt-14 flex justify-center gap-5 flex-wrap">

            <a href="#recorridos">

                <button class="bg-[#00d4ff] text-black px-10 py-4 rounded-full uppercase tracking-[0.2em] text-xs font-semibold hover:scale-105 transition duration-300">

                    Ver Recorridos

                </button>

            </a>
            

            <button class="border border-white/20 backdrop-blur px-10 py-4 rounded-full uppercase tracking-[0.2em] text-xs hover:bg-white hover:text-black transition duration-300">

                Ver Mapa

            </button>

        </div>

    </div>

</section>

<!-- PROXIMO BUS -->
<section class="py-20 px-6 bg-[#111111] border-y border-white/10">

    <div class="max-w-[1200px] mx-auto">

        <div class="bg-[#0f172a] border border-cyan-500/20 rounded-[35px] p-10">

            <h2 class="text-4xl md:text-5xl font-light mb-8">
                🚍 Próximo Bus
            </h2>

<?php

date_default_timezone_set("America/Santiago");

$sqlProximo = "SELECT TOP 1
                    b.patente,
                    r.nombre_ruta,
                    h.hora_salida
                FROM horarios h
                INNER JOIN buses b ON h.id_bus = b.id_bus
                INNER JOIN rutas r ON b.id_ruta = r.id_ruta
                WHERE h.hora_salida >= CAST(GETDATE() AS TIME)
                ORDER BY h.hora_salida";

$resultadoProximo = sqlsrv_query($conn, $sqlProximo);

$bus = sqlsrv_fetch_array($resultadoProximo, SQLSRV_FETCH_ASSOC);

if($bus){

    $horaBus = $bus["hora_salida"]->format('H:i');

    $actual = strtotime(date("H:i:s"));
    $salida = strtotime($horaBus);

    $min = round(($salida - $actual) / 60);

?>

            <div class="grid lg:grid-cols-3 gap-8">

                <div class="bg-black/30 rounded-3xl p-8 border border-white/10">

                    <div class="text-gray-400 uppercase text-xs tracking-[0.2em] mb-3">
                        Ruta
                    </div>

                    <div class="text-3xl font-light">
                        <?= $bus["nombre_ruta"] ?>
                    </div>

                </div>

                <div class="bg-black/30 rounded-3xl p-8 border border-white/10">

                    <div class="text-gray-400 uppercase text-xs tracking-[0.2em] mb-3">
                        Hora Salida
                    </div>

                    <div class="text-5xl font-light text-[#00d4ff]">
                        <?= $horaBus ?>
                    </div>

                </div>

                <div class="bg-black/30 rounded-3xl p-8 border border-white/10">

                    <div class="text-gray-400 uppercase text-xs tracking-[0.2em] mb-3">
                        Tiempo Restante
                    </div>

                    <div class="text-4xl font-light">
                        <?= $min ?> min
                    </div>

                </div>

            </div>

<?php } else { ?>

            <div class="text-2xl text-gray-400">
                No hay buses próximos disponibles.
            </div>

<?php } ?>

        </div>

    </div>

</section>

<!-- RECORRIDOS -->
<section id="recorridos" class="pt-32 pb-28 px-6 bg-[#0b0b0b]">

    <div class="max-w-[1400px] mx-auto">

        <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-10 mb-20">

            <div>

                <p class="uppercase tracking-[0.5em] text-[#00d4ff] text-xs mb-5">
                    Recorridos Disponibles
                </p>

                <h2 class="text-5xl md:text-7xl font-light leading-tight">

                    Explora
                    <br>
                    Las Rutas

                </h2>

            </div>

            <p class="text-gray-400 max-w-xl leading-relaxed text-lg">

                Consulta buses disponibles, horarios de salida,
                líneas de recorrido y monitoreo en tiempo real.

            </p>

        </div>

        <div class="grid lg:grid-cols-3 gap-8">

<?php

$sql = "SELECT 
            r.nombre_ruta,
            r.inicio,
            r.fin,
            b.patente,
            b.dueno_linea,
            h.hora_salida
        FROM buses b
        INNER JOIN rutas r ON b.id_ruta = r.id_ruta
        INNER JOIN horarios h ON b.id_bus = h.id_bus
        ORDER BY r.nombre_ruta, h.hora_salida";

$resultado = sqlsrv_query($conn, $sql);

while($row = sqlsrv_fetch_array($resultado, SQLSRV_FETCH_ASSOC)){

?>

            <div class="group relative overflow-hidden rounded-[35px] bg-[#111111] border border-white/10 hover:border-cyan-500/40 transition duration-500">

                <div class="p-10">

                    <p class="uppercase tracking-[0.35em] text-[#00d4ff] text-xs mb-4">

                        <?= $row["dueno_linea"] ?>

                    </p>

                    <h3 class="text-4xl font-light mb-6">

                        <?= $row["nombre_ruta"] ?>

                    </h3>

                    <div class="space-y-4 text-gray-300 text-lg">

                        <div>
                            📍 <?= $row["inicio"] ?> → <?= $row["fin"] ?>
                        </div>

                        <div>
                            🚌 Patente: <?= $row["patente"] ?>
                        </div>

                        <div>
                            ⏰ Salida: <?= $row["hora_salida"]->format('H:i') ?>
                        </div>

                    </div>

                    <button class="mt-8 border border-white/20 px-7 py-3 rounded-full uppercase tracking-[0.2em] text-xs hover:bg-white hover:text-black transition duration-300">

                        Ver en mapa

                    </button>

                </div>

            </div>

<?php } ?>

        </div>

    </div>

</section>

<!-- FOOTER -->
<footer class="bg-black py-16 px-6 border-t border-white/10">

    <div class="max-w-[1400px] mx-auto text-center">

        <div class="text-3xl tracking-[0.35em] font-light mb-6">
            VIENE LA MICRO
        </div>

        <p class="text-gray-500 leading-relaxed max-w-2xl mx-auto">

            Plataforma inteligente para monitoreo y visualización
            de buses en tiempo real en la Región de Ñuble.

        </p>

        <div class="text-gray-600 text-sm pt-10 uppercase tracking-[0.2em]">

            Sistema de Transporte Ñuble — 2026

        </div>

    </div>

</footer>

</body>
</html>