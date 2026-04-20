import * as THREE from "https://esm.run/three@0.161.0";
export function createStar(scene, defuse, color, halo) {
  const textureLoader = new THREE.TextureLoader();
  const cacheBuster = `?v=${Date.now()}`;
  const textureUrl = defuse.startsWith('http') 
    ? defuse 
    : `public/images/textures/objects/${defuse}${cacheBuster}`;

  const texture = textureLoader.load(textureUrl);

  const geometry = new THREE.SphereGeometry(1, 64, 64);
  
const plasmaTexture = textureLoader.load(`public/images/textures/plasma.png`);
  plasmaTexture.wrapS = plasmaTexture.wrapT = THREE.RepeatWrapping;

const starMaterial = new THREE.MeshBasicMaterial({
  map: texture,
  color: new THREE.Color(color),
});

  const star = new THREE.Mesh(geometry, starMaterial);
  scene.add(star);
  const timestamp = Date.now();
  const atmosphereMaterial = new THREE.ShaderMaterial({
    uniforms: {
      glowColor: { value: new THREE.Color(halo) },
      rimDir: { value: new THREE.Vector3(0.0, 0.0, 1.0) },
      time: { value: 0.0 }
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
  uniform float time;

  void main() {
    float rim = 1.0 - max(dot(vNormal, rimDir), 0.0);
    rim = pow(rim, 9.0 + 1.3 * sin(time * 4.0));
    float pulse = 1.3 + 0.3 * sin(time * 4.0);
    gl_FragColor = vec4(glowColor, rim * pulse);
  }
    // ${timestamp}
`,
    transparent: true,
    blending: THREE.AdditiveBlending,
    side: THREE.BackSide,
    depthWrite: false,
  });
  let atmosphere = null;
  atmosphere = new THREE.Mesh(
    new THREE.SphereGeometry(1.04, 64, 64),
    atmosphereMaterial
  );

  scene.add(atmosphere);

  return { star, atmosphere, atmosphereMaterial, starMaterial };
}
