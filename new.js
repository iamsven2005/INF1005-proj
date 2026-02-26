import * as THREE from "three";
import { PointerLockControls } from "three/examples/jsm/controls/PointerLockControls.js";

const scene = new THREE.Scene();
scene.background = new THREE.Color(0x87CEEB); // sky blue

const camera = new THREE.PerspectiveCamera(
  75,
  window.innerWidth / window.innerHeight,
  0.1,
  2000
);
const clock = new THREE.Clock();
const SPEED = 8; // units per second (increase for faster movement)
const keys = {};
let velocity = 0;
let steering = 0;
let heading = 0; // car direction (yaw)
const MAX_SPEED = 20;
const ACCEL = 18;
const FRICTION = 8;
const STEER_SPEED = 2.5;
let raceFinished = false;
const SPEED_TO_KMH = 12; // tweak scaling factor
const ROAD_WIDTH = 5;        // must match PlaneGeometry width
const ROAD_HALF = ROAD_WIDTH / 2;

const GRASS_SLOW_ZONE = ROAD_HALF - 0.5;  // edge before full collision
const HARD_COLLISION_LIMIT = ROAD_HALF-0.5;

let crashShake = 0;

window.addEventListener("keydown", (e) => {
  keys[e.key.toLowerCase()] = true;
});

window.addEventListener("keyup", (e) => {
  keys[e.key.toLowerCase()] = false;
});


const renderer = new THREE.WebGLRenderer({ antialias: true });
renderer.setSize(window.innerWidth, window.innerHeight);
document.body.appendChild(renderer.domElement);
// --- Speedometer UI
const speedUI = document.createElement("div");
speedUI.style.position = "fixed";
speedUI.style.bottom = "40px";
speedUI.style.left = "40px";
speedUI.style.width = "160px";
speedUI.style.height = "160px";
speedUI.style.borderRadius = "50%";
speedUI.style.border = "6px solid #444";
speedUI.style.background = "radial-gradient(circle at center, #111 60%, #000 100%)";
speedUI.style.boxShadow = "0 0 20px rgba(0,0,0,0.8)";
speedUI.style.color = "white";
speedUI.style.fontFamily = "monospace";
speedUI.style.display = "flex";
speedUI.style.alignItems = "center";
speedUI.style.justifyContent = "center";
speedUI.style.userSelect = "none";
document.body.appendChild(speedUI);

// Speed number
const speedText = document.createElement("div");
speedText.style.position = "absolute";
speedText.style.bottom = "30px";
speedText.style.width = "100%";
speedText.style.textAlign = "center";
speedText.style.fontSize = "18px";
speedText.innerText = "0 km/h";
speedUI.appendChild(speedText);

// Needle
const needle = document.createElement("div");
needle.style.position = "absolute";
needle.style.width = "4px";
needle.style.height = "70px";
needle.style.background = "red";
needle.style.top = "20px";
needle.style.left = "50%";
needle.style.transformOrigin = "50% 100%";
needle.style.transform = "rotate(-120deg)";
speedUI.appendChild(needle);

// --- Seat anchor (the camera will be attached here)
const seat = new THREE.Object3D();
seat.position.set(0, 1.2, 0); // "head height" in the car
scene.add(seat);

seat.add(camera);

// --- FPS look controls (mouse only)
const controls = new PointerLockControls(camera, document.body);

document.body.addEventListener("click", () => controls.lock());

// Optional: clamp vertical look angle (pitch)
controls.minPolarAngle = THREE.MathUtils.degToRad(50);  // looking up limit
controls.maxPolarAngle = THREE.MathUtils.degToRad(130); // looking down limit

// Basic lighting + something to see
scene.add(new THREE.HemisphereLight(0xffffff, 0x444444, 1.0));

const floor = new THREE.Mesh(
  new THREE.PlaneGeometry(200, 200),
  new THREE.MeshStandardMaterial({ color: 0x555555 })
);
floor.rotation.x = -Math.PI / 2;
scene.add(floor);

// A "dashboard" block in front of you
const dash = new THREE.Mesh(
  new THREE.BoxGeometry(1.6, 0.4, 0.6),
  new THREE.MeshStandardMaterial({ color: 0x3333aa })
);
dash.position.set(0, -0.35, -1.0);
seat.add(dash);

// --- Steering wheel (attach to DASH so it sits correctly)
const wheelGroup = new THREE.Group();

const wheelRing = new THREE.Mesh(
  new THREE.TorusGeometry(0.18, 0.03, 16, 48),
  new THREE.MeshStandardMaterial({ color: 0x111111, metalness: 0.2, roughness: 0.8 })
);

const wheelHub = new THREE.Mesh(
  new THREE.CylinderGeometry(0.05, 0.05, 0.03, 24),
  new THREE.MeshStandardMaterial({ color: 0x222222, metalness: 0.3, roughness: 0.7 })
);
wheelHub.rotation.x = Math.PI / 2; // face the camera

const spokeMat = new THREE.MeshStandardMaterial({ color: 0x1a1a1a, metalness: 0.4, roughness: 0.5 });
const spokeGeom = new THREE.BoxGeometry(0.02, 0.12, 0.01);

const spoke1 = new THREE.Mesh(spokeGeom, spokeMat);
spoke1.position.y = 0.07;

const spoke2 = new THREE.Mesh(spokeGeom, spokeMat);
spoke2.rotation.z = THREE.MathUtils.degToRad(120);
spoke2.position.set(Math.sin(THREE.MathUtils.degToRad(120)) * 0.07, Math.cos(THREE.MathUtils.degToRad(120)) * 0.07, 0);

const spoke3 = new THREE.Mesh(spokeGeom, spokeMat);
spoke3.rotation.z = THREE.MathUtils.degToRad(-120);
spoke3.position.set(Math.sin(THREE.MathUtils.degToRad(-120)) * 0.07, Math.cos(THREE.MathUtils.degToRad(-120)) * 0.07, 0);

wheelGroup.add(wheelRing, wheelHub, spoke1, spoke2, spoke3);

// Dash depth is 0.6, so front face (towards camera) is around +0.3 local Z.
// Put wheel slightly IN FRONT of that so it won't clip.
wheelGroup.position.set(0, 0.18, 0.36);
wheelGroup.rotation.x = THREE.MathUtils.degToRad(-20);

dash.add(wheelGroup);

const seatBase = new THREE.Mesh(
  new THREE.BoxGeometry(1.2, 0.5, 1.2),
  new THREE.MeshStandardMaterial({ color: 0x222222 })
);
seatBase.position.set(0, -0.9, -0.6); // below camera
seat.add(seatBase);

// --- Simple car interior: side doors + back plane

const interiorMat = new THREE.MeshStandardMaterial({ color: 0x2a2a2a, roughness: 0.9 });
const trimMat = new THREE.MeshStandardMaterial({ color: 0x1b1b1b, roughness: 0.8 });

// Left door (panel)
const leftDoor = new THREE.Mesh(
  new THREE.BoxGeometry(0.08, 0.8, 1.6),
  interiorMat
);
leftDoor.position.set(-0.75, -0.4, -0.7); // left, down, slightly forward/back
seat.add(leftDoor);

// Right door (panel)
const rightDoor = new THREE.Mesh(
  new THREE.BoxGeometry(0.08, 0.8, 1.6),
  interiorMat
);
rightDoor.position.set(0.75, -0.4, -0.7);
seat.add(rightDoor);

// Door armrests (optional detail)
const leftArm = new THREE.Mesh(new THREE.BoxGeometry(0.18, 0.08, 0.45), trimMat);
leftArm.position.set(-0.62, -0.55, -0.65);
seat.add(leftArm);

const rightArm = new THREE.Mesh(new THREE.BoxGeometry(0.18, 0.08, 0.45), trimMat);
rightArm.position.set(0.62, -0.55, -0.65);
seat.add(rightArm);

// Back plane (rear wall / seatback)
const backPlane = new THREE.Mesh(
  new THREE.PlaneGeometry(1.6, 1.0),
  interiorMat
);
backPlane.position.set(0, -0.35, 0.2); // behind camera (camera is at seat origin)
backPlane.rotation.y = Math.PI; // face towards camera
seat.add(backPlane);

// ---- Moving world (road + trees)
const world = new THREE.Group();
scene.add(world);

// Road
const road = new THREE.Mesh(
  new THREE.PlaneGeometry(10, 400),
  new THREE.MeshStandardMaterial({ color: 0x1b1b1b, roughness: 1 })
);
road.rotation.x = -Math.PI / 2;
road.position.y = 0.01; // slightly above floor to avoid z-fighting
road.position.z = -180;
world.add(road);

// Lane dashes (simple thin planes that move)
const laneDashes = [];
const dashMat = new THREE.MeshStandardMaterial({ color: 0xffffff, roughness: 1 });

for (let i = 0; i < 60; i++) {
  const d = new THREE.Mesh(new THREE.PlaneGeometry(0.12, 2.0), dashMat);
  d.rotation.x = -Math.PI / 2;
  d.position.set(0, 0.02, -i * 6 - 5); // spaced along -Z
  world.add(d);
  laneDashes.push(d);
}
// ---- Clouds
const clouds = [];

function makeCloud() {
  const cloud = new THREE.Group();

  const mat = new THREE.MeshStandardMaterial({
    color: 0xffffff,
    roughness: 1
  });

  for (let i = 0; i < 5; i++) {
    const puff = new THREE.Mesh(
      new THREE.SphereGeometry(1.2, 16, 16),
      mat
    );
    puff.position.set(
      (Math.random() - 0.5) * 2,
      Math.random() * 0.6,
      (Math.random() - 0.5) * 1
    );
    puff.scale.setScalar(0.6 + Math.random() * 0.6);
    cloud.add(puff);
  }

  return cloud;
}

for (let i = 0; i < 12; i++) {
  const c = makeCloud();
  c.position.set(
    (Math.random() - 0.5) * 60,
    15 + Math.random() * 8,
    -i * 60 - 40
  );
  world.add(c);
  clouds.push(c);
}
// ---- Spectator Stands
const stands = [];

function makeStand(side = 1) {
  const stand = new THREE.Group();

  const standWidth = 8;
  const standDepth = 18;
  const stepHeight = 0.35;
  const rows = 6;

  // --- Base platform (much lower now)
  const base = new THREE.Mesh(
    new THREE.BoxGeometry(standWidth, 0.6, standDepth),
    new THREE.MeshStandardMaterial({ color: 0x333333 })
  );
  base.position.y = 0.3;
  stand.add(base);

  // --- Tiered seating
  for (let r = 0; r < rows; r++) {
    const row = new THREE.Mesh(
      new THREE.BoxGeometry(standWidth - 0.5, stepHeight, 2.5),
      new THREE.MeshStandardMaterial({ color: 0x555555 })
    );
    row.position.y = 0.6 + r * stepHeight;
    row.position.z = -standDepth / 2 + r * 2.5 + 1.2;
    stand.add(row);

    // Add spectators on each row
    for (let i = 0; i < 12; i++) {
      const person = new THREE.Mesh(
        new THREE.BoxGeometry(0.25, 0.45, 0.25),
        new THREE.MeshStandardMaterial({
          color: new THREE.Color().setHSL(Math.random(), 0.6, 0.5)
        })
      );

      person.position.set(
        (Math.random() - 0.5) * (standWidth - 2),
        row.position.y + 0.35,
        row.position.z + (Math.random() - 0.5) * 1.5
      );

      stand.add(person);
    }
  }

  // Position stand
  stand.position.set(side * 14, 0, 0);
  return stand;
}

for (let i = 0; i < 6; i++) {
  const z = -i * 70 - 30;

  const leftStand = makeStand(-1);
  const rightStand = makeStand(1);

  leftStand.position.z = z;
  rightStand.position.z = z;

  leftStand.lookAt(0, leftStand.position.y, z);
  rightStand.lookAt(0, rightStand.position.y, z);

  // 🔁 Flip them because our geometry faces +Z
  leftStand.rotateY(Math.PI);
  rightStand.rotateY(Math.PI);

  world.add(leftStand, rightStand);
  stands.push(leftStand, rightStand);
}


// Trees (simple trunk + leaves) on both sides
const trees = [];
function makeTree() {
  const tree = new THREE.Group();

  const trunk = new THREE.Mesh(
    new THREE.CylinderGeometry(0.08, 0.1, 0.9, 10),
    new THREE.MeshStandardMaterial({ color: 0x5a3a1e, roughness: 1 })
  );
  trunk.position.y = 0.45;

  const leaves = new THREE.Mesh(
    new THREE.SphereGeometry(0.45, 14, 14),
    new THREE.MeshStandardMaterial({ color: 0x1f6b2a, roughness: 1 })
  );
  leaves.position.y = 1.15;

  tree.add(trunk, leaves);
  return tree;
}

for (let i = 0; i < 40; i++) {
  const tL = makeTree();
  const tR = makeTree();

  const z = -i * 12 - 10;
  const xOffset = 4.5 + Math.random() * 2.0;

  tL.position.set(-xOffset, 0, z);
  tR.position.set(+xOffset, 0, z);

  // slight random scale so they don't look identical
  const s1 = 0.8 + Math.random() * 0.6;
  const s2 = 0.8 + Math.random() * 0.6;
  tL.scale.setScalar(s1);
  tR.scale.setScalar(s2);

  world.add(tL, tR);
  trees.push(tL, tR);
}


// ---- Finish zone + podium
const finish = new THREE.Group();
world.add(finish);

// Place finish far down the road (-Z direction)
finish.position.set(0, 0, -360);

// Finish gate / marker (floating endpoint)
const endpoint = new THREE.Mesh(
  new THREE.SphereGeometry(0.35, 24, 24),
  new THREE.MeshStandardMaterial({ emissive: 0x00ffcc, emissiveIntensity: 2, color: 0x111111 })
);
endpoint.position.set(0, 1.6, 0);
finish.add(endpoint);

// Podium blocks (1st, 2nd, 3rd)
const podiumMat = new THREE.MeshStandardMaterial({ color: 0x888888, roughness: 0.6 });
const podium1 = new THREE.Mesh(new THREE.BoxGeometry(1.2, 0.8, 1.2), podiumMat);
const podium2 = new THREE.Mesh(new THREE.BoxGeometry(1.0, 0.5, 1.0), podiumMat);
const podium3 = new THREE.Mesh(new THREE.BoxGeometry(1.0, 0.35, 1.0), podiumMat);

podium1.position.set(0, 0.4, 0);
podium2.position.set(-1.4, 0.25, 0);
podium3.position.set(1.4, 0.175, 0);

finish.add(podium1, podium2, podium3);

// A simple finish banner
const banner = new THREE.Mesh(
  new THREE.BoxGeometry(6, 1, 0.2),
  new THREE.MeshStandardMaterial({ color: 0xffffff })
);
banner.position.set(0, 2.3, 0);
finish.add(banner);

// Light to make it pop
const finishLight = new THREE.PointLight(0x00ffcc, 2, 20);
finishLight.position.set(0, 2, 2);
finish.add(finishLight);


// --- Animate
function animate() {
  requestAnimationFrame(animate);

// --- Speedometer Update
const kmh = Math.abs(velocity) * SPEED_TO_KMH;
speedText.innerText = kmh.toFixed(0) + " km/h";

// Needle rotation range: -120° (0 kmh) to +120° (max)
const maxDisplaySpeed = MAX_SPEED * SPEED_TO_KMH;
const ratio = Math.min(kmh / maxDisplaySpeed, 1);
const angle = -120 + ratio * 240;

needle.style.transform = `rotate(${angle}deg)`;

  const dt = clock.getDelta();
// Float the endpoint
endpoint.position.y = 1.6 + Math.sin(performance.now() * 0.003) * 0.15;
endpoint.rotation.y += 0.8 * dt;
if (!raceFinished) {

  // --- ACCELERATION
  if (keys["w"]) velocity += ACCEL * dt;
  if (keys["s"]) velocity -= ACCEL * dt;

  velocity = Math.max(-MAX_SPEED * 0.4, Math.min(MAX_SPEED, velocity));

  // --- FRICTION
  if (!keys["w"] && !keys["s"]) {
    velocity *= 0.98;
    if (Math.abs(velocity) < 0.05) velocity = 0;
  }

  // --- STEERING INPUT
  if (keys["a"]) steering += STEER_SPEED * dt;
  if (keys["d"]) steering -= STEER_SPEED * dt;
}
  steering *= 0.85; // auto-center wheel
// --- Move clouds slowly forward and wrap
for (const c of clouds) {
  c.position.z += velocity * 0.2 * dt; // slower than ground
  if (c.position.z > 20) {
    c.position.z -= 700;
  }
}

  // Only turn when moving
  if (Math.abs(velocity) > 0.5) {
    heading += steering * velocity * 0.02 * dt;
  }

  // --- MOVE WORLD OPPOSITE OF CAR MOTION
  const moveX = Math.sin(heading) * velocity * dt;
  const moveZ = Math.cos(heading) * velocity * dt;

  world.position.x -= moveX;
  world.position.z += moveZ;
// Car lateral position relative to road
const carOffsetX = -world.position.x;

// --- Grass Slowdown
if (Math.abs(carOffsetX) > GRASS_SLOW_ZONE) {
  velocity *= 0.97; // friction from grass
}

// --- Hard Collision
if (Math.abs(carOffsetX) > HARD_COLLISION_LIMIT) {

  // Bounce back
  world.position.x = -Math.sign(carOffsetX) * HARD_COLLISION_LIMIT;

  velocity *= -0.3; // bounce backward
  crashShake = 0.3;
}

  // --- Rotate world to match heading
  world.rotation.y = heading;

  // --- Rotate steering wheel visually
  wheelGroup.rotation.z = steering * 2.5;
if (!raceFinished) {
  // Car's "progress" is world.position.z (since we move world opposite)
  // We reach finish when the finish group is near the camera in Z.
  const finishWorldZ = finish.position.z + world.position.z;

  if (finishWorldZ > -5) { // threshold near the driver
    raceFinished = true;
    velocity = 0;
    steering = 0;

    // Optional: snap camera look forward a bit (feel free to remove)
    // controls.getObject().rotation.y = 0;

    // Add a quick celebratory text overlay
    const win = document.createElement("div");
    win.innerText = "🏁 FINISH! 🎉";
    win.style.position = "fixed";
    win.style.top = "40px";
    win.style.left = "50%";
    win.style.transform = "translateX(-50%)";
    win.style.fontFamily = "monospace";
    win.style.fontSize = "44px";
    win.style.color = "white";
    win.style.textShadow = "0 0 18px rgba(0,255,204,0.9)";
    win.style.zIndex = "9999";
    document.body.appendChild(win);
  }
}

// If finished, stop input from moving the car
if (raceFinished) {
  // keep world steady; you can still look around with mouse
}

  renderer.render(scene, camera);
}
animate();




window.addEventListener("resize", () => {
  camera.aspect = window.innerWidth / window.innerHeight;
  camera.updateProjectionMatrix();
  renderer.setSize(window.innerWidth, window.innerHeight);
});
