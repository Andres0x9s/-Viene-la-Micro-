<?php
session_start();
include("conexion.php");

$adminLogged = isset($_SESSION["admin"]);
$filtroDireccion = $_GET["direccion"] ?? "";
$filtroRecorrido = $_GET["recorrido"] ?? "";
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
  <script type="importmap">
  {
    "imports": {
      "three": "https://cdn.jsdelivr.net/npm/three@0.160.0/build/three.module.js",
      "three/addons/": "https://cdn.jsdelivr.net/npm/three@0.160.0/examples/jsm/"
    }
  }
  </script>
  <link rel="stylesheet" href="assets/css/index.css">
</head>

<body>
  <div class="page-glow page-glow-one"></div>
  <div class="page-glow page-glow-two"></div>
  <div class="grid-bg"></div>

  <header class="site-header" id="inicio">
    <nav class="navbar">
      <a class="brand" href="#inicio">
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
        <li><a href="admin/login.php">Admin</a></li>
      </ul>

      <div class="auth-buttons">
        <?php if($adminLogged): ?>
          <a href="admin/dashboard.php" class="btn btn-ghost">Panel</a>
          <a href="admin/logout.php" class="btn btn-primary">Salir</a>
        <?php else: ?>
          <a href="conductores/login.php" class="btn btn-ghost">Conductor</a>
          <a href="admin/login.php" class="btn btn-ghost">Admin</a>
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

          <a href="conductores/login.php" class="btn btn-glass btn-large">
            Soy conductor <i class="fa-solid fa-id-card"></i>
          </a>

          <a href="admin/login.php" class="btn btn-glass btn-large">
            Panel admin <i class="fa-solid fa-user-shield"></i>
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
          <h2>🚍 Próximas Salidas</h2>
          <p>Filtra el recorrido que necesitas y revisa los próximos buses programados.</p>
        </div>

        <form method="GET" action="index.php#proximo-bus" class="filter-box">
          <select name="direccion" class="filter-select" onchange="this.form.submit()">
            <option value="">Todos los recorridos</option>

            <?php
            $sqlDirecciones = "SELECT DISTINCT direccion FROM horarios ORDER BY direccion";
            $resDirecciones = sqlsrv_query($conn, $sqlDirecciones);

            if($resDirecciones){
              while($dir = sqlsrv_fetch_array($resDirecciones, SQLSRV_FETCH_ASSOC)){
                $direccion = $dir["direccion"];
                $selected = ($filtroDireccion === $direccion) ? "selected" : "";
            ?>
              <option value="<?= htmlspecialchars($direccion) ?>" <?= $selected ?>>
                <?= htmlspecialchars($direccion) ?>
              </option>
            <?php
              }
            }
            ?>
          </select>

          <?php if($filtroDireccion): ?>
            <a href="index.php#proximo-bus" class="btn btn-glass">
              Limpiar filtro
            </a>
          <?php endif; ?>
        </form>

        <div class="routes-grid">
          <?php
          date_default_timezone_set("America/Santiago");

          $params = [];

          $sqlProximos = "SELECT TOP 6
                              b.patente,
                              b.dueno_linea,
                              r.nombre_ruta,
                              h.direccion,
                              h.hora_salida
                          FROM horarios h
                          INNER JOIN buses b ON h.id_bus = b.id_bus
                          INNER JOIN rutas r ON b.id_ruta = r.id_ruta
                          WHERE h.hora_salida >= CAST(GETDATE() AS TIME)";

          if($filtroDireccion !== ""){
            $sqlProximos .= " AND h.direccion = ?";
            $params[] = $filtroDireccion;
          }

          $sqlProximos .= " ORDER BY h.hora_salida";

          $resultadoProximos = sqlsrv_query($conn, $sqlProximos, $params);
          $hayProximos = false;

          if($resultadoProximos){
            while($bus = sqlsrv_fetch_array($resultadoProximos, SQLSRV_FETCH_ASSOC)){
              $hayProximos = true;
          ?>

          <article class="route-card">
            <div class="line-owner">
              <?= htmlspecialchars($bus["nombre_ruta"]) ?>
            </div>

            <h3><?= htmlspecialchars($bus["direccion"]) ?></h3>

            <div class="time-big">
              <?= $bus["hora_salida"]->format('H:i') ?>
            </div>

            <p>🚌 Patente: <?= htmlspecialchars($bus["patente"]) ?></p>
            <p>👤 Línea: <?= htmlspecialchars($bus["dueno_linea"]) ?></p>

            <a href="mapa.html" class="btn btn-glass">
              Ver en mapa <i class="fa-solid fa-location-dot"></i>
            </a>
          </article>

          <?php
            }
          }

          if(!$hayProximos){
          ?>

          <div class="empty-message">
            No hay más salidas disponibles para hoy
            <?= $filtroDireccion ? "en " . htmlspecialchars($filtroDireccion) : "" ?>.
          </div>

          <?php } ?>
        </div>
      </div>
    </section>

    <section class="data-section section-reveal" id="recorridos">
      <div class="section-heading">
        <span>Recorridos Disponibles</span>
        <h2>Explora las rutas</h2>
        <p>Consulta buses disponibles, horarios de salida, líneas de recorrido y monitoreo en tiempo real.</p>
      </div>

      <form method="GET" action="index.php#recorridos" class="filter-box">
        <select name="recorrido" class="filter-select" onchange="this.form.submit()">
          <option value="">Todos los recorridos</option>

          <?php
          $sqlRecorridosFiltro = "SELECT DISTINCT direccion FROM horarios ORDER BY direccion";
          $resRecorridosFiltro = sqlsrv_query($conn, $sqlRecorridosFiltro);

          if($resRecorridosFiltro){
            while($rec = sqlsrv_fetch_array($resRecorridosFiltro, SQLSRV_FETCH_ASSOC)){
              $recorrido = $rec["direccion"];
              $selectedRecorrido = ($filtroRecorrido === $recorrido) ? "selected" : "";
          ?>
            <option value="<?= htmlspecialchars($recorrido) ?>" <?= $selectedRecorrido ?>>
              <?= htmlspecialchars($recorrido) ?>
            </option>
          <?php
            }
          }
          ?>
        </select>

        <?php if($filtroRecorrido): ?>
          <a href="index.php#recorridos" class="btn btn-glass">
            Limpiar filtro
          </a>
        <?php endif; ?>
      </form>

      <div class="routes-grid">
        <?php
        $paramsRecorridos = [];

        $sql = "SELECT 
                    h.direccion,
                    r.inicio,
                    r.fin,
                    b.patente,
                    b.dueno_linea,
                    h.hora_salida
                FROM buses b
                INNER JOIN rutas r ON b.id_ruta = r.id_ruta
                INNER JOIN horarios h ON b.id_bus = h.id_bus";

        if($filtroRecorrido !== ""){
          $sql .= " WHERE h.direccion = ?";
          $paramsRecorridos[] = $filtroRecorrido;
        }

        $sql .= " ORDER BY r.nombre_ruta, h.hora_salida";

        $resultado = sqlsrv_query($conn, $sql, $paramsRecorridos);
        $hayRecorridos = false;

        if($resultado){
          while($row = sqlsrv_fetch_array($resultado, SQLSRV_FETCH_ASSOC)){
            $hayRecorridos = true;
        ?>

        <article class="route-card">
          <div class="line-owner">
            <?= htmlspecialchars($row["dueno_linea"]) ?>
          </div>

          <h3><?= htmlspecialchars($row["direccion"]) ?></h3>

          <p>📍 <?= htmlspecialchars($row["inicio"]) ?> → <?= htmlspecialchars($row["fin"]) ?></p>
          <p>🚌 Patente: <?= htmlspecialchars($row["patente"]) ?></p>
          <p>⏰ Salida: <?= $row["hora_salida"]->format('H:i') ?></p>

          <a href="mapa.html" class="btn btn-glass">
            Ver en mapa <i class="fa-solid fa-location-dot"></i>
          </a>
        </article>

        <?php
          }
        }

        if(!$hayRecorridos){
        ?>

        <div class="empty-message">
          No hay recorridos disponibles
          <?= $filtroRecorrido ? "para " . htmlspecialchars($filtroRecorrido) : "" ?>.
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
      <a href="#proximo-bus" class="btn btn-primary btn-large">Buscar salida</a>
    </section>
  </main>

  <footer class="footer" id="contacto">
    <div>
      <h3>Viene la Micro</h3>
      <p>Sistema de consulta y ubicación para transporte público.</p>
    </div>

    <p>© 2026 - Proyecto académico.</p>
  </footer>

  <div id="busTransition" class="bus-transition">
    <div class="bus-transition-text">Cargando ruta...</div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      document.querySelectorAll("a[href]").forEach(link => {
        const href = link.getAttribute("href");

        if(
          !href ||
          !href.includes("mapa.html") ||
          href.startsWith("#") ||
          href.startsWith("javascript:") ||
          href.startsWith("mailto:") ||
          href.startsWith("tel:") ||
          link.target === "_blank"
        ){
          return;
        }

        link.addEventListener("click", e => {
          e.preventDefault();

          if(window.startBusCrashTransition){
            window.startBusCrashTransition(href);
          }else{
            const transition = document.getElementById("busTransition");
            transition?.classList.add("active");
            setTimeout(() => {
              window.location.href = href;
            }, 650);
          }
        });
      });
    });
  </script>

  <script src="assets/js/main.js"></script>
  <script type="module" src="assets/js/micro3d.js"></script>
</body>
</html>

