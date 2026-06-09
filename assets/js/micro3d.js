import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import { MeshoptDecoder } from 'three/addons/libs/meshopt_decoder.module.js';

const canvas = document.getElementById('bus-canvas');
const wrapper = canvas?.parentElement;
const transitionOverlay = document.getElementById('busTransition');

if (canvas && wrapper) {

  const scene = new THREE.Scene();

  const getResponsiveScene = () => {
    const width = window.innerWidth || wrapper.clientWidth || 1;
    const height = window.innerHeight || wrapper.clientHeight || 1;
    const aspect = width / height;
    const compact = width <= 780;
    const tiny = width <= 460;
    const narrow = aspect < 0.82;

    return {
      modelTargetDimension: tiny ? 13.8 : compact ? 15.4 : narrow ? 16.2 : 18.8,
      baseX: compact ? 0.2 : narrow ? 0.65 : 2.35,
      baseY: compact ? 1.35 : 2.15,
      baseZ: compact ? 0.4 : 1,
      introX: compact ? 18 : 28,
      introY: compact ? -0.85 : -1.05,
      introZ: compact ? -6 : -7,
      crashReadyY: compact ? 0.08 : 0.35,
      crashReadyZ: compact ? -2.4 : -1.6,
      crashImpactY: compact ? 0.12 : 0.75,
      crashImpactZ: compact ? 18.6 : 21.5,
      crashScale: tiny ? 4.35 : compact ? 4.9 : narrow ? 5.35 : 6.35,
      cameraIdleY: compact ? 2.55 : 3.1,
      cameraIdleZ: compact ? 29 : 26,
      cameraCrashY: compact ? 1.7 : 2.45,
      cameraCrashZ: compact ? 25.4 : 24.2,
      lookAtY: compact ? 0.24 : 0
    };
  };

  let view = getResponsiveScene();

  const camera = new THREE.PerspectiveCamera(
    38,
    1,
    0.1,
    1000
  );

  camera.position.set(0, view.cameraIdleY, view.cameraIdleZ);

  const renderer = new THREE.WebGLRenderer({
    canvas,
    alpha: true,
    antialias: true
  });

  renderer.setPixelRatio(
    Math.min(window.devicePixelRatio, 2)
  );

  renderer.outputColorSpace = THREE.SRGBColorSpace;
  renderer.toneMapping = THREE.ACESFilmicToneMapping;
  renderer.toneMappingExposure = 1.22;

  const hemiLight = new THREE.HemisphereLight(
    0xffffff,
    0x0b1224,
    2.6
  );
  scene.add(hemiLight);

  const keyLight = new THREE.DirectionalLight(
    0xffffff,
    4.2
  );
  keyLight.position.set(10, 12, 12);
  scene.add(keyLight);

  const fillLight = new THREE.DirectionalLight(
    0x00e5ff,
    2.8
  );
  fillLight.position.set(-12, 6, 8);
  scene.add(fillLight);

  const rimLight = new THREE.PointLight(
    0x8b5cf6,
    5,
    80
  );
  rimLight.position.set(8, 5, -12);
  scene.add(rimLight);

  const crashLight = new THREE.PointLight(
    0x00e5ff,
    0,
    120
  );
  crashLight.position.set(0, 2, 18);
  scene.add(crashLight);

  let bus = null;
  let baseScale = 1;
  let modelMaxDimension = 1;
  let modelReady = false;

  const basePosition = new THREE.Vector3();
  const baseRotation = new THREE.Euler(0.02, -2.35, 0);
  const introStartPosition = new THREE.Vector3();
  const crashFrontRotation = new THREE.Euler(0, -Math.PI / 2, 0);
  const crashReadyPosition = new THREE.Vector3();
  const crashImpactPosition = new THREE.Vector3();

  const updateResponsiveTargets = () => {
    view = getResponsiveScene();
    basePosition.set(view.baseX, view.baseY, view.baseZ);
    introStartPosition.set(view.introX, view.introY, view.introZ);
    crashReadyPosition.set(0, view.crashReadyY, view.crashReadyZ);
    crashImpactPosition.set(0, view.crashImpactY, view.crashImpactZ);

    if (modelReady && modelMaxDimension > 0) {
      baseScale = view.modelTargetDimension / modelMaxDimension;
    }
  };

  updateResponsiveTargets();

  const intro = {
    active: true,
    start: 0,
    duration: 2.15
  };

  const crash = {
    active: false,
    start: 0,
    duration: 4.15,
    url: null
  };

  const easeOutCubic = (t) => 1 - Math.pow(1 - t, 3);
  const easeOutBack = (t) => {
    const c1 = 1.70158;
    const c3 = c1 + 1;
    return 1 + c3 * Math.pow(t - 1, 3) + c1 * Math.pow(t - 1, 2);
  };

  const loader = new GLTFLoader();
  loader.setMeshoptDecoder(MeshoptDecoder);

  loader.load(
    'assets/models/bus3D.glb',

    (gltf) => {

      bus = gltf.scene;
      scene.add(bus);

      const box = new THREE.Box3().setFromObject(bus);
      const center = box.getCenter(new THREE.Vector3());
      const size = box.getSize(new THREE.Vector3());

      bus.position.sub(center);

      modelMaxDimension = Math.max(
        size.x,
        size.y,
        size.z
      );

      baseScale = view.modelTargetDimension / modelMaxDimension;
      bus.scale.setScalar(baseScale);

      bus.rotation.copy(baseRotation);
      bus.position.copy(introStartPosition);
      wrapper.classList.add('bus-arriving');

      bus.traverse((child) => {
        if (child.isMesh) {
          child.castShadow = true;
          child.receiveShadow = true;
        }
      });

      intro.start = clock.getElapsedTime();
      modelReady = true;

      document.body.classList.add('bus-loaded');
      console.log('Modelo cargado correctamente');
    },

    undefined,

    (error) => {
      console.error(
        'No se pudo cargar el modelo 3D:',
        error
      );
    }
  );

  const resize = () => {

    updateResponsiveTargets();

    const width = wrapper.clientWidth;
    const height = wrapper.clientHeight;

    camera.aspect = width / height;
    camera.updateProjectionMatrix();

    renderer.setSize(
      width,
      height,
      false
    );
  };

  window.addEventListener('resize', resize);
  resize();

  const pointer = {
    x: 0,
    y: 0
  };

  window.addEventListener(
    'pointermove',
    (event) => {

      pointer.x =
        (event.clientX / window.innerWidth - 0.5) * 2;

      pointer.y =
        (event.clientY / window.innerHeight - 0.5) * 2;
    }
  );

  window.startBusCrashTransition = (url) => {
    if (!modelReady || crash.active) {
      transitionOverlay?.classList.add('active');
      setTimeout(() => {
        window.location.href = url;
      }, 550);
      return;
    }

    crash.active = true;
    crash.start = clock.getElapsedTime();
    crash.url = url;
    transitionOverlay?.classList.add('active');
    wrapper.classList.remove('bus-arriving');
    document.body.classList.add('bus-crashing');
    document.body.classList.add('bus-crash-slow');

    requestAnimationFrame(() => {
      resize();
      requestAnimationFrame(resize);
    });
  };

  const clock = new THREE.Clock();

  function animate() {

    requestAnimationFrame(animate);

    const elapsed = clock.getElapsedTime();

    if (bus) {

      if (crash.active) {
        const raw = Math.min(
          (elapsed - crash.start) / crash.duration,
          1
        );

        /*
          Transición en 3 etapas:
          1) El bus se endereza y queda mirando de frente.
          2) Hace una pausa breve ya frontal.
          3) Avanza hacia la cámara y genera el choque.
        */
        const alignRaw = Math.min(raw / 0.48, 1);
        const align = easeOutCubic(alignRaw);
        const pauseRaw = Math.min(Math.max((raw - 0.48) / 0.18, 0), 1);
        const driveRaw = Math.min(Math.max((raw - 0.66) / 0.34, 0), 1);
        const drive = easeOutCubic(driveRaw);
        const impactMix = Math.max((raw - 0.84) / 0.16, 0);
        const shake = Math.sin(raw * Math.PI * 20) * impactMix * (1 - raw) * 0.12;

        bus.rotation.x = THREE.MathUtils.lerp(baseRotation.x, crashFrontRotation.x, align);
        bus.rotation.y = THREE.MathUtils.lerp(baseRotation.y, crashFrontRotation.y, align) + shake;
        bus.rotation.z = THREE.MathUtils.lerp(0.08, 0, align) + Math.sin(raw * Math.PI * 8) * impactMix * (1 - raw) * 0.035;

        const readyX = THREE.MathUtils.lerp(basePosition.x, crashReadyPosition.x, align);
        const readyY = THREE.MathUtils.lerp(basePosition.y, crashReadyPosition.y, align);
        const readyZ = THREE.MathUtils.lerp(basePosition.z, crashReadyPosition.z, align);

        bus.position.x = THREE.MathUtils.lerp(readyX, crashImpactPosition.x, drive);
        bus.position.y = THREE.MathUtils.lerp(readyY, crashImpactPosition.y, drive);
        bus.position.z = THREE.MathUtils.lerp(readyZ, crashImpactPosition.z, drive);

        const alignScale = THREE.MathUtils.lerp(1, 1.18, align);
        const driveScale = THREE.MathUtils.lerp(alignScale, view.crashScale, drive);
        bus.scale.setScalar(baseScale * driveScale);
        crashLight.intensity = 1.5 + 22 * drive;

        camera.position.x += (0 - camera.position.x) * 0.08;
        camera.position.y += (view.cameraCrashY - camera.position.y) * 0.08;
        camera.position.z += (view.cameraCrashZ - camera.position.z) * 0.055;

        if (raw >= 1 && crash.url) {
          window.location.href = crash.url;
        }
      } else {
        let introMix = 1;

        if (intro.active) {
          const rawIntro = Math.min(
            (elapsed - intro.start) / intro.duration,
            1
          );
          introMix = easeOutBack(rawIntro);

          if (rawIntro >= 1) {
            intro.active = false;
            introMix = 1;
            wrapper.classList.remove('bus-arriving');
          }
        }

        const floatY = Math.sin(elapsed * 1.6) * 0.14;
        const idleRotY = Math.sin(elapsed * 0.75) * 0.08;
        const idleRotX = Math.sin(elapsed * 1.1) * 0.018;

        bus.rotation.y =
          baseRotation.y +
          idleRotY +
          pointer.x * 0.035;

        bus.rotation.x =
          baseRotation.x +
          idleRotX -
          pointer.y * 0.018;

        bus.rotation.z = 0;

        bus.position.x = introStartPosition.x + (basePosition.x - introStartPosition.x) * introMix;
        bus.position.y = introStartPosition.y + (basePosition.y + floatY - introStartPosition.y) * introMix;
        bus.position.z = introStartPosition.z + (basePosition.z - introStartPosition.z) * introMix;

        bus.scale.setScalar(baseScale * (0.88 + 0.12 * introMix));
        crashLight.intensity += (0 - crashLight.intensity) * 0.08;

        camera.position.x +=
          (pointer.x * 0.35 - camera.position.x) *
          0.025;

        camera.position.y +=
          (view.cameraIdleY - pointer.y * 0.28 - camera.position.y) *
          0.025;

        camera.position.z +=
          (view.cameraIdleZ - camera.position.z) *
          0.025;
      }
    }

    camera.lookAt(0, view.lookAtY, 0);

    renderer.render(
      scene,
      camera
    );
  }

  animate();
}
