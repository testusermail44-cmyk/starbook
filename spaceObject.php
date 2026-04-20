<?php
session_start();
include('header.php');
include('models/objectModel.php');
$object = getInfoAboutObject($_GET['id']);
$star = null;
if ($object['tid'] == 3) {
  $star = getInfoAboutStar($object['id']);
}
?>
<html lang="uk">

<head>
  <meta charset="UTF-8" />
  <link rel="stylesheet" href="styles/header.css" />
  <link rel="stylesheet" href="styles/viewer.css" />
  <link rel="stylesheet" href="styles/style.css" />
  <link rel="stylesheet" href="styles/font-awesome.css" />
  <title><?= $object['name'] ?></title>
</head>

<body>

  <div class="view">
    <div class="center a l">
      <div class="object-name bbrg">
        <div class="o-t"><?= $object['type'] ?></div>
        <label class="o-n"><?= $object['name'] ?></label>
      </div>
    </div>
    <div class="hide info-panel vc bbrg">
      <div class="hc c fn">
        <img class="img-i bbrg" src="<?= (strpos($image, 'http') === 0) ? $image : "public/images/objects/" . ($image ?: 'default.png') ?>" />
        <div class="vc">
          <?php
          $parameters = explode("\n", $object['parameters']);
          foreach ($parameters as $p) {
            ?>
            <span class="t"><?= preg_replace('/\^(\d+)/', '<sup>$1</sup>', $p); ?></span>
            <?php
          }
          ?>
        </div>
      </div>
      <div class="content t">
        <?= $object['description'] ?>
      </div>
    </div>
    <div class="sidepanel">
      <div class="sidebutton bbrg eject"></div>
    </div>
  </div>
  <div id="imgModal" class="modal">
    <span class="close">&times;</span>
    <img class="modal-content" id="modalImg">
  </div>

  <script>
    const bigImg = document.querySelector('.img-i');
    const modal = document.getElementById('imgModal');
    const modalImg = document.getElementById('modalImg');
    const closeBtn = document.querySelector('.modal .close');

    bigImg.addEventListener('click', () => {
      modal.style.display = "flex";
      modalImg.src = bigImg.src;
    });

    closeBtn.addEventListener('click', () => modal.style.display = "none");
    modal.addEventListener('click', (e) => {
      if (e.target === modal) modal.style.display = "none";
    });
  </script>
  <script src="js/ejectButton.js"></script>
  <script type="module">
    import * as THREE from "https://esm.run/three@0.161.0";
    import { OrbitControls } from "https://esm.run/three@0.161.0/examples/jsm/controls/OrbitControls.js";
    import { EffectComposer } from "https://esm.run/three@0.161.0/examples/jsm/postprocessing/EffectComposer.js";
    import { RenderPass } from "https://esm.run/three@0.161.0/examples/jsm/postprocessing/RenderPass.js";
    import { UnrealBloomPass } from "https://esm.run/three@0.161.0/examples/jsm/postprocessing/UnrealBloomPass.js";
    import { ShaderPass } from "https://esm.run/three@0.161.0/examples/jsm/postprocessing/ShaderPass.js";

    import { createPlanet } from "./js/planet.js";
    import { createBlackHole } from "./js/blackhole.js";
    import { createStar } from "./js/star.js";
    import { createGalaxy } from "./js/galaxy.js";

    const showPlanet = <?= ($object['tid'] == 1 || $object['tid'] == 4 || $object['tid'] == 5 || $object['tid'] == 6 || $object['tid'] == 7) ? 'true' : 'false' ?>;
    const showBlackHole = <?= $object['tid'] == 8 ? 'true' : 'false' ?>;
    const showStar = <?= $object['tid'] == 3 ? 'true' : 'false' ?>;
    const showGalaxy = <?= $object['tid'] == 2 ? 'true' : 'false' ?>;

    const scene = new THREE.Scene();

    const basePath = "public/images/textures/cubemap/";
    const sides = ["px", "nx", "py", "ny", "pz", "nz"];
    const urls = sides.map((side) => `${basePath}${side}.jpg`);

    const loader = new THREE.CubeTextureLoader();
    const cubeTexture = loader.load(urls);
    cubeTexture.encoding = THREE.sRGBEncoding;
    scene.background = cubeTexture;

    const camera = new THREE.PerspectiveCamera(
      75,
      window.innerWidth / (window.innerHeight - 60),
      0.1,
      1000
    );
    camera.position.z = 4;

    const renderer = new THREE.WebGLRenderer({
      antialias: true,
      alpha: false,
    });
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    renderer.toneMapping = THREE.ACESFilmicToneMapping;
    renderer.toneMappingExposure = 1.0;
    renderer.setSize(window.innerWidth, window.innerHeight - 60);
    renderer.setPixelRatio(window.devicePixelRatio || 1);
    document.querySelector(".view").appendChild(renderer.domElement);

    const renderScene = new RenderPass(scene, camera);

    const bloomPass = new UnrealBloomPass(
      new THREE.Vector2(window.innerWidth, window.innerHeight),
      1.5, // strength
      0.4, // radius
      0.85 // threshold
    );
    bloomPass.threshold = 0;
    bloomPass.strength = 0.5; 
    bloomPass.radius = 2.5;

    const renderTarget = new THREE.WebGLRenderTarget(
      window.innerWidth,
      window.innerHeight,
      {
        samples: 4, 
      }
    );
    const composer = new EffectComposer(renderer, renderTarget);
    composer.setSize(window.innerWidth, window.innerHeight);
    composer.addPass(renderScene);
    composer.addPass(bloomPass);

    const controls = new OrbitControls(camera, renderer.domElement);
    controls.enableDamping = true;
    controls.dampingFactor = 0.05;
    controls.enablePan = false;
    controls.minDistance = 2; //2
    controls.maxDistance = 2.5; //2.5

    let isEjected = false;
    let animating = false;

    const initialCameraPos = new THREE.Vector3(0, 0, 4);
    const initialTarget = new THREE.Vector3(0, 0, 0);

    let savedUserCameraPos = initialCameraPos.clone();
    let savedUserCameraQuat = new THREE.Quaternion();
    let savedUserTarget = initialTarget.clone();

    const ejectedCameraPos = new THREE.Vector3(
      0.266049610585932,
      0.9821253596114693,
      1.6821575574468453
    );
    const ejectedCameraRot = new THREE.Euler(
      -0.5284588208365605,
      -0.44667814748688217,
      -0.24705404994326946,
      "XYZ"
    );
    const ejectedTarget = new THREE.Vector3(1.1990137376882968, 0, 0);
    const ejectedCameraQuat = new THREE.Quaternion().setFromEuler(
      ejectedCameraRot
    );
 
    let targetPos = camera.position.clone();
    let targetQuat = camera.quaternion.clone();
    let targetVector = controls.target.clone();

    const light = new THREE.DirectionalLight(0xffffff, 1);
    light.position.set(4, 4, 6);
    if (!showStar) {
      scene.add(light);
    }
    const ambient = new THREE.AmbientLight(0x222222, 0.3);
    if (!showStar) {
      scene.add(ambient);
    }
    let planet, atmosphere, atmosphereMaterial;
    if (showPlanet) {
      ({ planet, atmosphere, atmosphereMaterial } = createPlanet(scene, "<?= $object['defuse'] ?>", "<?= $object['normal'] ?>", <?= isset($object['atmosphereColor']) ? $object['atmosphereColor'] : '0xFFFFFF' ?>, "<?= $object['atmosphere'] ?>", "<?= $object['model'] ?>"));
    }

    let star, starMaterial;
    if (showStar) {
      ({ star, atmosphere, atmosphereMaterial, starMaterial } = createStar(scene, "<?= isset($star['texture']) ? $star['texture'] : '' ?>", <?= isset($star['color']) ? $star['color'] : 0xFFFFFF ?>, <?= isset($star['halo']) ? $star['halo'] : 0xFFFFFF ?>));
    }

    let blackHoleMesh, accretionDisk, diskMat, lensingShader;
    let lensingPass;
    if (showBlackHole) {
      ({ blackHoleMesh, accretionDisk, diskMat, lensingShader } = createBlackHole(scene));
      lensingPass = new ShaderPass(lensingShader);
      composer.addPass(lensingPass);
    }

    let galaxy;
    if (showGalaxy) {
      ({ galaxy } = createGalaxy(scene));
    }

    let ejectBtn = document.querySelector(".eject");
    ejectBtn.addEventListener("click", () => {
      if (animating) return;

      isEjected = !isEjected;
      animating = true;

      if (isEjected) {
         
        savedUserCameraPos.copy(camera.position);
        savedUserCameraQuat.copy(camera.quaternion);
        savedUserTarget.copy(controls.target);

         
        targetPos.copy(ejectedCameraPos);
        targetQuat.copy(ejectedCameraQuat);
        targetVector.copy(ejectedTarget);
        if (showPlanet || showStar) {
          atmosphereMaterial.uniforms.rimDir.value.set(0.5, 0.1, 1.0);
          atmosphereMaterial.uniforms.rimDir.value.needsUpdate = true;
        }
        controls.enabled = false;
      } else {
        
        targetPos.copy(savedUserCameraPos);
        targetQuat.copy(savedUserCameraQuat);
        targetVector.copy(savedUserTarget);
        if (showPlanet || showStar) {
          atmosphereMaterial.uniforms.rimDir.value.set(0.0, 0.0, 1.0);
          atmosphereMaterial.uniforms.rimDir.value.needsUpdate = true;
        }
        controls.enabled = true;
      }
    });

   
    let lastTime = performance.now();
    const clock = new THREE.Clock();
    function animate(now) {
      requestAnimationFrame(animate);
      const dt = (now - lastTime) / 1000;
      lastTime = now;
      const elapsedTime = clock.getElapsedTime();
       
      if (showPlanet && planet) {
        planet.rotation.y += 0.0003;
        atmosphere.rotation.y += 0.0003;
      }
      if (showGalaxy && galaxy) {
        galaxy.updateScale(camera);
      }
      if (showStar) {
        star.rotation.y += 0.0003;
        atmosphere.rotation.y += 0.0003;
        atmosphereMaterial.uniforms.time.value = clock.getElapsedTime();
      }
      if (showBlackHole && diskMat) {
        diskMat.uniforms.uTime.value = elapsedTime;

       
        const blackHoleScreenPosVec3 = blackHoleMesh.position.clone().project(camera);
        lensingPass.uniforms.blackHoleScreenPos.value.set(
          (blackHoleScreenPosVec3.x + 1) / 2,
          (blackHoleScreenPosVec3.y + 1) / 2
        );
        diskMat.uniforms.uTime.value += 0.01;
        accretionDisk.rotation.z += 0.005;
      }

       
      if (animating) {
        const posDiff = camera.position.distanceTo(targetPos);
        const quatDiff = 1 - Math.abs(camera.quaternion.dot(targetQuat));

        camera.position.lerp(targetPos, 0.05);
        camera.quaternion.slerp(targetQuat, 0.05);
        controls.target.lerp(targetVector, 0.05);
 
        if (posDiff < 0.01 && quatDiff < 0.01) {
          animating = false;
        }
      }

      controls.update();
      composer.render();
    }
    animate();

   
    renderer.domElement.addEventListener("contextmenu", (event) => {
      event.preventDefault();
      console.log("=== DEBUG INFO ===");
      console.log("Camera Position:", camera.position);
      console.log("Camera Rotation:", camera.rotation);
      console.log("Controls Target:", controls.target);
      console.log("Is Ejected:", isEjected);
      console.log("Animating:", animating);
    });
 
    window.addEventListener("resize", () => {
      const height = window.innerHeight - 60;
      camera.aspect = window.innerWidth / height;
      renderer.setSize(window.innerWidth, height);
      composer.setSize(window.innerWidth, height);
      camera.updateProjectionMatrix();
      if (showBlackHole) {
        lensingPass.uniforms.aspectRatio.value = window.innerWidth / window.innerHeight;
      }
    });
  </script>
</body>

</html>
