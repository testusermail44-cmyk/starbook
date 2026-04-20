import * as THREE from "https://esm.run/three@0.161.0";

export function createBlackHole(scene) {
const BLACK_HOLE_RADIUS = 0.5;
const DISK_INNER_RADIUS = BLACK_HOLE_RADIUS + 0.1;
const DISK_OUTER_RADIUS = 1.2;
const DISK_TILT_ANGLE = Math.PI / 1;

const blackHoleGeom = new THREE.SphereGeometry(BLACK_HOLE_RADIUS, 128, 64);
const blackHoleMat = new THREE.MeshBasicMaterial({ color: 0x000000 });
const blackHoleMesh = new THREE.Mesh(blackHoleGeom, blackHoleMat);
scene.add(blackHoleMesh);

const diskGeom = new THREE.RingGeometry(DISK_INNER_RADIUS, DISK_OUTER_RADIUS, 512, 256);
const diskMat = new THREE.ShaderMaterial({
    uniforms: {
        uTime: { value: 0 },
        uColorHot: { value: new THREE.Color(0x1a0a05) },
        uColorMid1: { value: new THREE.Color(0xff7732) },
        uColorMid2: { value: new THREE.Color(0xffb347) },
        uColorOuter: { value: new THREE.Color(0xfff6e0) },
        uNoiseScale: { value: 1.5 },
        uFlowSpeed: { value: 0.22 },
        uDensity: { value: 1.2 }
    },
    vertexShader: `
        varying vec2 vUv;
        varying float vRadius;
        varying float vAngle;
        void main() {
            vUv = uv;
            vRadius = length(position.xy);
            vAngle = atan(position.y, position.x);
            gl_Position = projectionMatrix * modelViewMatrix * vec4(position, 1.0);
        }
    `,
    fragmentShader: `
        uniform float uTime;
        uniform vec3 uColorHot;
        uniform vec3 uColorMid1;
        uniform vec3 uColorMid2;
        uniform vec3 uColorOuter;
        uniform float uNoiseScale;
        uniform float uFlowSpeed;
        uniform float uDensity;
        varying vec2 vUv;
        varying float vRadius;
        varying float vAngle;

        // --- Simplex Noise ---
        vec3 mod289(vec3 x){return x - floor(x * (1.0 / 289.0)) * 289.0;}
        vec4 mod289(vec4 x){return x - floor(x * (1.0 / 289.0)) * 289.0;}
        vec4 permute(vec4 x){return mod289(((x*34.0)+1.0)*x);}
        vec4 taylorInvSqrt(vec4 r){return 1.79284291400159 - 0.85373472095314 * r;}
        float snoise(vec3 v){
            const vec2 C = vec2(1.0/6.0,1.0/3.0);
            const vec4 D = vec4(0.0,0.5,1.0,2.0);
            vec3 i  = floor(v + dot(v, C.yyy) );
            vec3 x0 = v - i + dot(i, C.xxx) ;
            vec3 g = step(x0.yzx, x0.xyz);
            vec3 l = 1.0 - g;
            vec3 i1 = min(g.xyz, l.zxy);
            vec3 i2 = max(g.xyz, l.zxy);
            vec3 x1 = x0 - i1 + C.xxx;
            vec3 x2 = x0 - i2 + C.yyy;
            vec3 x3 = x0 - D.yyy;
            i = mod289(i);
            vec4 p = permute( permute( permute( i.z + vec4(0.0,i1.z,i2.z,1.0)) + i.y + vec4(0.0,i1.y,i2.y,1.0)) + i.x + vec4(0.0,i1.x,i2.x,1.0));
            float n_ = 0.142857142857;
            vec3  ns = n_ * D.wyz - D.xzx;
            vec4 j = p - 49.0 * floor(p * ns.z * ns.z);
            vec4 x_ = floor(j * ns.z);
            vec4 y_ = floor(j - 7.0 * x_);
            vec4 x = x_ *ns.x + ns.yyyy;
            vec4 y = y_ *ns.x + ns.yyyy;
            vec4 h = 1.0 - abs(x) - abs(y);
            vec4 b0 = vec4( x.xy, y.xy );
            vec4 b1 = vec4( x.zw, y.zw );
            vec4 s0 = floor(b0)*2.0 + 1.0;
            vec4 s1 = floor(b1)*2.0 + 1.0;
            vec4 sh = -step(h, vec4(0.0));
            vec4 a0 = b0.xzyw + s0.xzyw*sh.xxyy;
            vec4 a1 = b1.xzyw + s1.xzyw*sh.zzww;
            vec3 p0 = vec3(a0.xy,h.x);
            vec3 p1 = vec3(a0.zw,h.y);
            vec3 p2 = vec3(a1.xy,h.z);
            vec3 p3 = vec3(a1.zw,h.w);
            vec4 norm = taylorInvSqrt(vec4(dot(p0,p0),dot(p1,p1),dot(p2,p2),dot(p3,p3)));
            p0 *= norm.x; p1 *= norm.y; p2 *= norm.z; p3 *= norm.w;
            vec4 m = max(0.6 - vec4(dot(x0,x0),dot(x1,x1),dot(x2,x2),dot(x3,x3)),0.0);
            m = m*m;
            return 42.0*dot( m*m, vec4( dot(p0,x0), dot(p1,x1), dot(p2,x2), dot(p3,x3) ) );
        }

        void main() {
            float normalizedRadius = smoothstep(${DISK_INNER_RADIUS.toFixed(2)}, ${DISK_OUTER_RADIUS.toFixed(2)}, vRadius);
            float spiral = vAngle * 3.0 - (1.0 / (normalizedRadius + 0.1)) * 2.0;
            vec2 noiseUv = vec2(vUv.x + uTime*uFlowSpeed*(2.0/(vRadius*0.3+1.0)) + sin(spiral)*0.1, vUv.y*0.8 + cos(spiral)*0.1);
            float noiseVal = snoise(vec3(noiseUv*uNoiseScale,uTime*0.15));
            noiseVal = (noiseVal+1.0)*0.5;
            vec3 color = uColorOuter;
            color = mix(color,uColorMid2,smoothstep(0.0,0.5,normalizedRadius));
            color = mix(color,uColorMid1,smoothstep(0.5,0.75,normalizedRadius));
            color = mix(color,uColorHot,smoothstep(0.7,0.95,normalizedRadius));
            color *= (0.5+noiseVal*1.0);
            float brightness = pow(1.0-normalizedRadius,1.0)*3.5+0.5;
            brightness *= (0.3+noiseVal*2.2);
            float pulse = sin(uTime*1.8+normalizedRadius*12.0+vAngle*2.0)*0.15+0.85;
            brightness *= pulse;
            float alpha = uDensity*(0.2+noiseVal*0.9);
            alpha *= smoothstep(0.0,0.15,normalizedRadius);
            alpha *= (1.0-smoothstep(0.85,1.0,normalizedRadius));
            alpha = clamp(alpha,0.0,1.0);
            gl_FragColor = vec4(color*brightness,alpha);
        }
    `,
    transparent: true,
    side: THREE.DoubleSide,
    depthWrite: false,
    blending: THREE.AdditiveBlending
});
const accretionDisk = new THREE.Mesh(diskGeom, diskMat);
accretionDisk.rotation.x = DISK_TILT_ANGLE;
scene.add(accretionDisk);

const lensingShader = {
    uniforms: {
        "tDiffuse": { value: null },
        "blackHoleScreenPos": { value: new THREE.Vector2(0.5, 0.5) },
        "lensingStrength": { value: 0.22 },
        "lensingRadius": { value: 0.18 },
        "aspectRatio": { value: window.innerWidth/window.innerHeight },
        "chromaticAberration": { value: 0.005 }
    },
    vertexShader: `varying vec2 vUv; void main(){vUv=uv;gl_Position=projectionMatrix*modelViewMatrix*vec4(position,1.0);}`,
    fragmentShader: `
        uniform sampler2D tDiffuse;
        uniform vec2 blackHoleScreenPos;
        uniform float lensingStrength;
        uniform float lensingRadius;
        uniform float aspectRatio;
        uniform float chromaticAberration;
        varying vec2 vUv;
        void main(){
            vec2 screenPos=vUv;
            vec2 toCenter=screenPos-blackHoleScreenPos;
            toCenter.x*=aspectRatio;
            float dist=length(toCenter);
            float distortionAmount=lensingStrength/(dist*dist+0.003);
            distortionAmount=clamp(distortionAmount,0.0,0.7);
            float falloff=smoothstep(lensingRadius,lensingRadius*0.3,dist);
            distortionAmount*=falloff;
            vec2 offset=normalize(toCenter)*distortionAmount;
            offset.x/=aspectRatio;
            vec2 distortedUvR=screenPos-offset*(1.0+chromaticAberration);
            vec2 distortedUvG=screenPos-offset;
            vec2 distortedUvB=screenPos-offset*(1.0-chromaticAberration);
            float r=texture2D(tDiffuse,distortedUvR).r;
            float g=texture2D(tDiffuse,distortedUvG).g;
            float b=texture2D(tDiffuse,distortedUvB).b;
            gl_FragColor=vec4(r,g,b,1.0);
        }
    `
};

return { blackHoleMesh, accretionDisk, diskMat, lensingShader };
}