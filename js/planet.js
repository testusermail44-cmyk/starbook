import * as THREE from "https://esm.run/three@0.161.0";
import { FBXLoader } from "https://esm.run/three@0.161.0/examples/jsm/loaders/FBXLoader.js";
export function createPlanet(
  scene,
  defuse,
  normal,
  atmosphereColor,
  isAtmosphere,
  model
) {
  const textureLoader = new THREE.TextureLoader();
  const cacheBuster = `?v=${Date.now()}`;
const getTexturePath = (fileName) => {
  return fileName.startsWith('http') 
    ? fileName 
    : `public/images/textures/objects/${fileName}${cacheBuster}`;
};

const planetTexture = textureLoader.load(getTexturePath(defuse));

const planetNormal = textureLoader.load(getTexturePath(normal));


  let planet;

if (model === "sphere") {
  const geometry = new THREE.SphereGeometry(1, 64, 64);
  const material = new THREE.MeshStandardMaterial({
    map: planetTexture,
    normalMap: planetNormal,
  });
  planet = new THREE.Mesh(geometry, material);
  scene.add(planet);
} else {
  const loader = new FBXLoader();
  loader.load(`public/models/${model}`, (object) => {
    object.scale.set(0.0201, 0.0201, 0.0201);
    object.traverse((child) => {
      if (child.isMesh) {
        child.material.map = planetTexture;
        child.material.normalMap = planetNormal;
        child.material.side = THREE.DoubleSide;
        child.material.roughness = 1;
        child.material.metalness = 0;  
        child.material.transparent = false;
      }
    });
    scene.add(object);
    planet = object;
  });
}

   
  const timestamp = Date.now();
  const atmosphereMaterial = new THREE.ShaderMaterial({
    uniforms: {
      glowColor: { value: new THREE.Color(atmosphereColor) },
      rimDir: { value: new THREE.Vector3(0.0, 0.0, 1.0) },
    },
    vertexShader: `
    varying vec3 vNormal;
    void main() {
      vNormal = normalize(normalMatrix * normal);
      gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 0.99);
    }
        // ${timestamp}
  `,
    fragmentShader: `
    varying vec3 vNormal;
    uniform vec3 glowColor;
    uniform vec3 rimDir;
    void main() {
      float rim = 1.0 - max(dot(vNormal, rimDir), 0.0);
      rim = pow(rim, 10.0);
      gl_FragColor = vec4(glowColor, rim * 0.6);
    }
      // ${timestamp}
  `,
    transparent: true,
    blending: THREE.AdditiveBlending,
    side: THREE.BackSide,
    depthWrite: false,
  });
  let atmosphere = null;
  if (isAtmosphere) {
    atmosphere = new THREE.Mesh(
      new THREE.SphereGeometry(1.03, 64, 64),
      atmosphereMaterial
    );

    scene.add(atmosphere);
  }
  return { planet, atmosphere, atmosphereMaterial };
}
