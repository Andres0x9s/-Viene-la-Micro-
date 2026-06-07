import * as THREE from 'three';
import { GLTFLoader } from 'three/addons/loaders/GLTFLoader.js';
import { MeshoptDecoder } from 'three/addons/libs/meshopt_decoder.module.js';

const canvas = document.getElementById('bus-canvas');
const wrapper = canvas?.parentElement;

if (canvas && wrapper) {

  const scene = new THREE.Scene();

  const camera = new THREE.PerspectiveCamera(
    38,
    1,
    0.1,
    1000
  );

  camera.position.set(0, 3.1, 26);

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
  renderer.toneMappingExposure = 1.15;

  // Luces

  const hemiLight = new THREE.HemisphereLight(
    0xffffff,
    0x0b1224,
    2.4
  );
  scene.add(hemiLight);

  const keyLight = new THREE.DirectionalLight(
    0xffffff,
    3.8
  );
  keyLight.position.set(10, 12, 12);
  scene.add(keyLight);

  const fillLight = new THREE.DirectionalLight(
    0x00e5ff,
    2.4
  );
  fillLight.position.set(-12, 6, 8);
  scene.add(fillLight);

  const rimLight = new THREE.PointLight(
    0x8b5cf6,
    4,
    70
  );

  rimLight.position.set(8, 5, -12);
  scene.add(rimLight);

  let bus = null;

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

      const maxDimension = Math.max(
        size.x,
        size.y,
        size.z
      );

      const targetDimension = 18.8;
      const scale = targetDimension / maxDimension;

      bus.scale.setScalar(scale);

      // Ángulo tipo imagen de referencia
      bus.rotation.set(
        0.02,
        -2.35,
        0
      );

      bus.position.y = 0.15;

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

  const clock = new THREE.Clock();

  function animate() {

    requestAnimationFrame(animate);

    const elapsed = clock.getElapsedTime();

    if (bus) {

      bus.rotation.y =
        -2.35 +
        Math.sin(elapsed * 0.75) * 0.08 +
        pointer.x * 0.035;

      bus.rotation.x =
        0.02 +
        Math.sin(elapsed * 1.1) * 0.018 -
        pointer.y * 0.018;

      bus.position.y =
        0.15 +
        Math.sin(elapsed * 1.6) * 0.14;
    }

    camera.position.x +=
      (pointer.x * 0.35 - camera.position.x) *
      0.025;

    camera.position.y +=
      (3.1 - pointer.y * 0.28 - camera.position.y) *
      0.025;

    camera.lookAt(0, 0, 0);

    renderer.render(
      scene,
      camera
    );
  }

  animate();
}