import * as THREE from "https://esm.run/three@0.161.0";
 
const NUM_STARS = 2500;
const GALAXY_THICKNESS = 0.08;
const CORE_X_DIST = 0.15;
const CORE_Y_DIST = 0.15;
const OUTER_CORE_X_DIST = 0.4;
const OUTER_CORE_Y_DIST = 0.4;
const ARM_X_DIST = 0.5;
const ARM_Y_DIST = 0.25;
const ARM_X_MEAN = 0.8;
const ARM_Y_MEAN = 0.4;
const SPIRAL = 4.0;
const ARMS = 2.0;
const HAZE_RATIO = 1;
 
const STAR_MIN = 0.002;
const STAR_MAX = 0.015;
const HAZE_MAX = 0.15;
const HAZE_MIN = 0.05;
const HAZE_OPACITY = 0.25;
 
const starTypes = {
  percentage: [60.0, 20.0, 10.0, 5.0, 3.0, 2.0],
  color: [
    0xffddaa,  
    0xfff4e0,  
    0xffffff,  
    0xffe4b5,  
    0xcce0ff,  
    0xaaccff   
  ],
  size: [1.0, 1.2, 1.5, 1.3, 1.8, 2.0],
};
 
function gaussianRandom(mean = 0, stdev = 1) {
  let u = 1 - Math.random();
  let v = Math.random();
  let z = Math.sqrt(-2.0 * Math.log(u)) * Math.cos(2.0 * Math.PI * v);
  return z * stdev + mean;
}

function clamp(value, minimum, maximum) {
  return Math.min(maximum, Math.max(minimum, value));
}

function spiral(x, y, z, offset) {
  let r = Math.sqrt(x ** 2 + y ** 2);
  let theta = offset;
  theta += x > 0 ? Math.atan(y / x) : Math.atan(y / x) + Math.PI;
  theta += (r / ARM_X_DIST) * SPIRAL;
  return new THREE.Vector3(r * Math.cos(theta), r * Math.sin(theta), z);
}
 
function createStarTexture() {
  const canvas = document.createElement('canvas');
  canvas.width = 64;
  canvas.height = 64;
  const ctx = canvas.getContext('2d');
  
  const gradient = ctx.createRadialGradient(32, 32, 0, 32, 32, 32);
  gradient.addColorStop(0, 'rgba(255,255,255,1)');
  gradient.addColorStop(0.2, 'rgba(255,255,255,0.8)');
  gradient.addColorStop(0.4, 'rgba(255,255,255,0.4)');
  gradient.addColorStop(1, 'rgba(255,255,255,0)');
  
  ctx.fillStyle = gradient;
  ctx.fillRect(0, 0, 64, 64);
  
  const texture = new THREE.CanvasTexture(canvas);
  return texture;
}

function createHazeTexture() {
  const canvas = document.createElement('canvas');
  canvas.width = 128;
  canvas.height = 128;
  const ctx = canvas.getContext('2d');
  
  const gradient = ctx.createRadialGradient(64, 64, 0, 64, 64, 64);
  gradient.addColorStop(0, 'rgba(255,255,255,0.6)');
  gradient.addColorStop(0.3, 'rgba(255,255,255,0.3)');
  gradient.addColorStop(0.7, 'rgba(255,255,255,0.1)');
  gradient.addColorStop(1, 'rgba(255,255,255,0)');
  
  ctx.fillStyle = gradient;
  ctx.fillRect(0, 0, 128, 128);
  
  const texture = new THREE.CanvasTexture(canvas);
  return texture;
}
 
const starTexture = createStarTexture();
const hazeTexture = createHazeTexture();
 
const starMaterials = starTypes.color.map(
  (color) =>
    new THREE.SpriteMaterial({
      map: starTexture,
      color: color,
      transparent: true,
      blending: THREE.AdditiveBlending,
      depthWrite: false,
    })
);
 
const hazeMaterial = new THREE.SpriteMaterial({
  map: hazeTexture,
  color: 0xb0c4de,  
  opacity: HAZE_OPACITY,
  transparent: true,
  blending: THREE.AdditiveBlending,
  depthTest: false,
  depthWrite: false,
});
 
class Star {
  constructor(position) {
    this.position = position;
    this.starType = this.generateStarType();
    this.obj = null;
  }

  generateStarType() {
    let num = Math.random() * 100.0;
    let pct = starTypes.percentage;
    for (let i = 0; i < pct.length; i++) {
      num -= pct[i];
      if (num < 0) {
        return i;
      }
    }
    return 0;
  }

  updateScale(camera) {
    if (!this.obj) return;
    let dist = this.position.distanceTo(camera.position);
    let starSize = (dist / 100) * starTypes.size[this.starType];
    starSize = clamp(starSize, STAR_MIN, STAR_MAX);
    this.obj.scale.set(starSize, starSize, starSize);
  }

  toThreeObject(scene) {
    let sprite = new THREE.Sprite(starMaterials[this.starType]);
    sprite.position.copy(this.position);
     
    const baseSize = starTypes.size[this.starType] * 0.008;
    sprite.scale.set(baseSize, baseSize, baseSize);
    
    this.obj = sprite;
    scene.add(sprite);
  }
}
 
class Haze {
  constructor(position) {
    this.position = position;
    this.obj = null;
  }

  updateScale(camera) {
    if (!this.obj) return;
    let dist = this.position.distanceTo(camera.position);
    let opacity = clamp(HAZE_OPACITY * Math.pow(dist / 1.5, 2), 0, HAZE_OPACITY);
    this.obj.material.opacity = opacity;
  }

  toThreeObject(scene) {
    let sprite = new THREE.Sprite(hazeMaterial.clone());
    sprite.position.copy(this.position);
    
     
    const cloudSize = clamp(HAZE_MAX * Math.random(), HAZE_MIN, HAZE_MAX);
    sprite.scale.set(cloudSize, cloudSize, cloudSize);
    
    this.obj = sprite;
    scene.add(sprite);
  }
}
 
class Galaxy {
  constructor(scene) {
    this.scene = scene;
    this.stars = this.generateObject(NUM_STARS, (pos) => new Star(pos));
    this.haze = this.generateObject(NUM_STARS * HAZE_RATIO, (pos) => new Haze(pos));

 

    this.stars.forEach((star) => star.toThreeObject(scene));
    this.haze.forEach((haze) => haze.toThreeObject(scene));
  }

  updateScale(camera) {
    this.stars.forEach((star) => {
      star.updateScale(camera);
    });

    this.haze.forEach((haze) => {
      haze.updateScale(camera);
    });
  }

  generateObject(numStars, generator) {
    let objects = [];

     
    for (let i = 0; i < numStars / 4; i++) {
      let pos = new THREE.Vector3(
        gaussianRandom(0, CORE_X_DIST),
        gaussianRandom(0, CORE_Y_DIST),
        gaussianRandom(0, GALAXY_THICKNESS)
      );
      let obj = generator(pos);
      objects.push(obj);
    }

     
    for (let i = 0; i < numStars / 4; i++) {
      let pos = new THREE.Vector3(
        gaussianRandom(0, OUTER_CORE_X_DIST),
        gaussianRandom(0, OUTER_CORE_Y_DIST),
        gaussianRandom(0, GALAXY_THICKNESS)
      );
      let obj = generator(pos);
      objects.push(obj);
    }

    
    for (let j = 0; j < ARMS; j++) {
      for (let i = 0; i < numStars / 4; i++) {
        let pos = spiral(
          gaussianRandom(ARM_X_MEAN, ARM_X_DIST),
          gaussianRandom(ARM_Y_MEAN, ARM_Y_DIST),
          gaussianRandom(0, GALAXY_THICKNESS),
          (j * 2 * Math.PI) / ARMS
        );
        let obj = generator(pos);
        objects.push(obj);
      }
    }

    return objects;
  }
}

 
export function createGalaxy(scene) {
  const galaxy = new Galaxy(scene);
  
   
  const galaxyLight = new THREE.PointLight(0xffeedd, 0.5, 5);
  galaxyLight.position.set(0, 0, 0);
  scene.add(galaxyLight);
  
  return { galaxy, galaxyLight };
}