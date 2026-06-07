<?php
session_start();
include("conexion.php");

$logged = isset($_SESSION['usuario']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Viene la Micro | Transporte inteligente</title>
  <link rel="icon" type="image/png" href="busicono.png">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">

  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">

  <style>
    html{
      scroll-behavior:smooth;
    }

    .data-section{
      padding:110px 7%;
      position:relative;
      z-index:5;
    }

    .data-card{
      background:rgba(15,23,42,.72);
      border:1px solid rgba(255,255,255,.1);
      border-radius:34px;
      padding:42px;
      backdrop-filter:blur(18px);
      box-shadow:0 24px 80px rgba(0,0,0,.35);
    }

    .next-grid{
      display:grid;
      grid-template-columns:repeat(3,1fr);
      gap:22px;
      margin-top:34px;
    }

    .next-box{
      background:rgba(0,0,0,.28);
      border:1px solid rgba(255,255,255,.08);
      border-radius:24px;
      padding:28px;
    }

    .next-box small{
      display:block;
      color:#94a3b8;
      text-transform:uppercase;
      letter-spacing:.18em;
      font-size:11px;
      margin-bottom:12px;
    }

    .next-box strong{
      color:white;
      font-size:32px;
      font-weight:700;
    }

    .next-box .cyan{
      color:#25f4ff;
      font-size:42px;
    }

    .routes-grid{
      display:grid;
      grid-template-columns:repeat(3,1fr);
      gap:24px;
      margin-top:45px;
    }

    .route-card{
      background:linear-gradient(145deg,rgba(15,23,42,.82),rgba(2,6,23,.84));
      border:1px solid rgba(255,255,255,.1);
      border-radius:30px;
      padding:32px;
      transition:.3s ease;
      box-shadow:0 18px 50px rgba(0,0,0,.28);
    }

    .route-card:hover{
      transform:translateY(-8px);
      border-color:rgba(37,244,255,.45);
      box-shadow:0 22px 70px rgba(37,244,255,.12);
    }

    .route-card .line-owner{
      color:#25f4ff;
      text-transform:uppercase;
      letter-spacing:.25em;
      font-size:11px;
      font-weight:800;
      margin-bottom:16px;
    }

    .route-card h3{
      color:white;
      font-size:30px;
      margin-bottom:22px;
    }

    .route-card p{
      color:#cbd5e1;
      margin:12px 0;
      font-size:15px;
    }

    .route-card a{
      margin-top:22px;
      display:inline-flex;
      align-items:center;
      gap:10px;
    }

    .empty-message{
      color:#94a3b8;
      font-size:20px;
      margin-top:25px;
    }

    @media(max-width:950px){
      .next-grid,
      .routes-grid{
        grid-template-columns:1fr;
      }

      .data-section{
        padding:80px 5%;
      }

      .data-card{
        padding:28px;
      }
    }
  </style>

  <script type="importmap">
  {
    "imports": {
      "three": "https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js",
      "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/"
    }
  }
  </script>
</head>

<body>
  <div class="page-glow page-glow-one"></div>
  <div class="page-glow page-glow-two"></div>
  <div class="grid-bg"></div>

  <header class="site-header" id="inicio">
    <nav class="navbar">
      <a class="brand" href="#inicio" aria-label="Inicio Viene la Micro">
        <span class="brand-icon"><i class="fa-solid fa-bus-simple"></i></span>
        <span>Viene la Micro</span>
      </a>

      <button class="menu-toggle" id="menuToggle" aria-label="Abrir menú">
        <i class="fa-solid fa-bars"></i>
      </button>

      <ul class="nav-links" id="navLinks">
        <li><a href="#inicio">Inicio</a></li>
        <li><a href="#recorridos">Recorridos</a></li>
        <li><a href="#proximo-bus">Horarios</a></li>
        <li><a href="mapa.html">Mapa</a></li>
      </ul>

      <div class="auth-buttons">
        <?php if($logged): ?>
          <a href="admin/dashboard.php" class="btn btn-ghost">Panel</a>
          <a href="admin/logout.php" class="btn btn-primary">Salir</a>
        <?php else: ?>
          <a href="#recorridos" class="btn btn-primary">Ver Buses</a>
        <?php endif; ?>
      </div>
    </nav>
  </header>

  <main>
    <section class="hero section-reveal">
      <div class="hero-content">
        <div class="eyebrow"><span></span> Sistema inteligente de transporte</div>

        <h1>Tu micro, más cerca que nunca.</h1>

        <p class="hero-text">
          Consulta recorridos, horarios y ubicación en tiempo real de buses de Coihueco, Cato y Chillán.
        </p>

        <div class="hero-actions">
          <a href="#recorridos" class="btn btn-primary btn-large">
            Ver Recorridos <i class="fa-solid fa-arrow-right"></i>
          </a>

          <a href="mapa.html" class="btn btn-glass btn-large">
            Ver Mapa <i class="fa-solid fa-location-dot"></i>
          </a>
        </div>

        <div class="hero-stats">
          <div>
            <strong>GPS</strong>
            <span>Ubicación</span>
          </div>
          <div>
            <strong>24/7</strong>
            <span>Consulta</span>
          </div>
          <div>
            <strong>Web</strong>
            <span>Responsive</span>
          </div>
        </div>
      </div>

      <div class="hero-visual" aria-label="Modelo 3D de micro">
        <div class="orbit orbit-one"></div>
        <div class="orbit orbit-two"></div>

        <canvas id="bus-canvas"></canvas>

        <div class="floating-card card-route">
          <i class="fa-solid fa-route"></i>
          <span>Ruta activa</span>
        </div>

        <div class="floating-card card-gps">
          <i class="fa-solid fa-satellite-dish"></i>
          <span>GPS online</span>
        </div>
      </div>
    </section>

    <section class="data-section section-reveal" id="proximo-bus">
      <div class="data-card">
        <div class="section-heading">
          <span>Horarios</span>
          <h2>🚍 Próximo Bus</h2>
          <p>Consulta el bus más cercano según la hora actual.</p>
        </div>

        <?php
        date_default_timezone_set("America/Santiago");

        $sqlProximo = "SELECT TOP 1
                            b.patente,
                            l.direccion,
                            h.hora_salida
                        FROM horarios h
                        INNER JOIN buses b ON h.id_bus = b.id_bus
                        INNER JOIN rutas r ON b.id_ruta = r.id_ruta
                        INNER JOIN horarios l ON b.id_bus = l.id_bus
                        WHERE h.hora_salida >= CAST(GETDATE() AS TIME)
                        ORDER BY h.hora_salida";

        $resultadoProximo = sqlsrv_query($conn, $sqlProximo);
        $bus = $resultadoProximo ? sqlsrv_fetch_array($resultadoProximo, SQLSRV_FETCH_ASSOC) : null;

        if($bus){
          $horaBus = $bus["hora_salida"]->format('H:i');

          $actual = strtotime(date("H:i:s"));
          $salida = strtotime($horaBus);
          $min = round(($salida - $actual) / 60);
        ?>

        <div class="next-grid">
          <div class="next-box">
            <small>Ruta</small>
            <strong><?= $bus["direccion"] ?></strong>
          </div>

          <div class="next-box">
            <small>Hora Salida</small>
            <strong class="cyan"><?= $horaBus ?></strong>
          </div>

          <div class="next-box">
            <small>Tiempo Restante</small>
            <strong><?= $min ?> min</strong>
          </div>
        </div>

        <?php } else { ?>

        <div class="empty-message">
          No hay buses próximos disponibles.
        </div>

        <?php } ?>
      </div>
    </section>

    <section class="data-section section-reveal" id="recorridos">
      <div class="section-heading">
        <span>Recorridos Disponibles</span>
        <h2>Explora las rutas</h2>
        <p>Consulta buses disponibles, horarios de salida, líneas de recorrido y monitoreo en tiempo real.</p>
      </div>

      <div class="routes-grid">
        <?php
        $sql = "SELECT 
                    h.direccion,
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

        if($resultado){
          while($row = sqlsrv_fetch_array($resultado, SQLSRV_FETCH_ASSOC)){
        ?>

        <article class="route-card">
          <div class="line-owner">
            <?= $row["dueno_linea"] ?>
          </div>

          <h3><?= $row["direccion"] ?></h3>

          <p>📍 <?= $row["inicio"] ?> → <?= $row["fin"] ?></p>
          <p>🚌 Patente: <?= $row["patente"] ?></p>
          <p>⏰ Salida: <?= $row["hora_salida"]->format('H:i') ?></p>

          <a href="mapa.html" class="btn btn-glass">
            Ver en mapa <i class="fa-solid fa-location-dot"></i>
          </a>
        </article>

        <?php
          }
        } else {
        ?>

        <div class="empty-message">
          No se pudieron cargar los recorridos.
        </div>

        <?php } ?>
      </div>
    </section>

    <section class="experience section-reveal">
      <div>
        <span class="eyebrow"><span></span> Experiencia 3D</span>
        <h2>Un diseño con presencia, movimiento y estilo tecnológico.</h2>
      </div>

      <p>
        Inspirado en landing pages animadas, este inicio usa profundidad visual,
        tarjetas glassmorphism, efectos de brillo y un modelo 3D de micro para darle más personalidad al proyecto.
      </p>
    </section>

    <section class="cta section-reveal">
      <h2>¿Listo para encontrar tu próxima micro?</h2>
      <p>Entra al sistema y revisa la información disponible al tiro.</p>
      <a href="#recorridos" class="btn btn-primary btn-large">Comenzar ahora</a>
    </section>
  </main>

  <footer class="footer" id="contacto">
    <div>
      <h3>Viene la Micro</h3>
      <p>Sistema de consulta y ubicación para transporte público.</p>
    </div>

    <p>© 2026 - Proyecto académico.</p>
  </footer>

  <script src="assets/js/main.js"></script>
  <script type="module" src="assets/js/micro3d.js"></script>
</body>
</html>