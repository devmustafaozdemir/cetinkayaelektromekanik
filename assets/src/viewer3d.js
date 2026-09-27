/**
 * Lazy-loaded 3D viewer. Builds parametric models from the same specs as the
 * server-side 2D drawings (app/models.php): "tank:<variant>", "booster:<n>",
 * "pump:<horizontal|vertical|circulator>", "sub:<deep|drain>".
 *
 * Build: npm run build:3d  →  assets/js/viewer3d.js
 */
import {
  WebGLRenderer, Scene, PerspectiveCamera, Group, Mesh, InstancedMesh, Object3D, Box3, Vector3,
  BoxGeometry, CylinderGeometry, SphereGeometry, TorusGeometry, PlaneGeometry, CircleGeometry, TubeGeometry,
  CatmullRomCurve3, MeshStandardMaterial, MeshBasicMaterial, HemisphereLight, DirectionalLight, Color,
  PMREMGenerator, CanvasTexture, SRGBColorSpace, ACESFilmicToneMapping, DoubleSide, MathUtils,
} from 'three';
import { OrbitControls } from 'three/examples/jsm/controls/OrbitControls.js';
import { RoomEnvironment } from 'three/examples/jsm/environments/RoomEnvironment.js';

/* ---------- Materials ---------- */
const M = {
  steel: () => new MeshStandardMaterial({ color: 0xd3d9df, metalness: 0.9, roughness: 0.28 }),
  steelDark: () => new MeshStandardMaterial({ color: 0x8a949f, metalness: 0.85, roughness: 0.35 }),
  paint: () => new MeshStandardMaterial({ color: 0x2d5686, metalness: 0.35, roughness: 0.45 }),
  dark: () => new MeshStandardMaterial({ color: 0x1b2838, metalness: 0.3, roughness: 0.6 }),
  frame: () => new MeshStandardMaterial({ color: 0x2d3d52, metalness: 0.4, roughness: 0.55 }),
  red: () => new MeshStandardMaterial({ color: 0xd7171d, metalness: 0.2, roughness: 0.4 }),
  white: () => new MeshStandardMaterial({ color: 0xe9edf1, metalness: 0.1, roughness: 0.5 }),
  screen: () => new MeshBasicMaterial({ color: 0x0f3b2c }),
  led: (c) => new MeshBasicMaterial({ color: c }),
  water: () => new MeshStandardMaterial({ color: 0x2b7fb8, metalness: 0, roughness: 0.2, transparent: true, opacity: 0.8 }),
};

const TANK = {
  galvaniz: { color: 0xc3cad2, metalness: 0.78, roughness: 0.42, rib: 0x9aa4af },
  paslanmaz: { color: 0xe4e8ec, metalness: 0.92, roughness: 0.2, rib: 0xb6bec7 },
  grp: { color: 0x6c9cbc, metalness: 0.0, roughness: 0.55, rib: 0x4f7c9b },
  sandvic: { color: 0xeeeeea, metalness: 0.05, roughness: 0.5, rib: 0xc6c7c1 },
};

/* ---------- Helpers ---------- */
function mesh(geo, mat, x = 0, y = 0, z = 0) {
  const m = new Mesh(geo, mat);
  m.position.set(x, y, z);
  return m;
}
function cylY(r, h, mat, x = 0, y = 0, z = 0, seg = 40) { // y = bottom
  return mesh(new CylinderGeometry(r, r, h, seg), mat, x, y + h / 2, z);
}
function cylX(r, len, mat, x = 0, y = 0, z = 0, seg = 32) { // x = centre
  const m = mesh(new CylinderGeometry(r, r, len, seg), mat, x, y, z);
  m.rotation.z = Math.PI / 2;
  return m;
}
function cylZ(r, len, mat, x = 0, y = 0, z = 0, seg = 32) {
  const m = mesh(new CylinderGeometry(r, r, len, seg), mat, x, y, z);
  m.rotation.x = Math.PI / 2;
  return m;
}
function box(w, h, d, mat, x = 0, y = 0, z = 0) { // y = bottom
  return mesh(new BoxGeometry(w, h, d), mat, x, y + h / 2, z);
}

/** Cooling fins around a motor body along Y. */
function finsY(g, r, y0, h, count, mat) {
  const geo = new BoxGeometry(0.012, h, r * 0.18);
  const inst = new InstancedMesh(geo, mat, count);
  const o = new Object3D();
  for (let i = 0; i < count; i++) {
    const a = (i / count) * Math.PI * 2;
    o.position.set(Math.cos(a) * r, y0 + h / 2, Math.sin(a) * r);
    o.rotation.set(0, -a, 0);
    o.updateMatrix();
    inst.setMatrixAt(i, o.matrix);
  }
  g.add(inst);
}
function finsX(g, r, x0, len, count, y, z, mat) {
  const geo = new BoxGeometry(len, 0.012, r * 0.18);
  const inst = new InstancedMesh(geo, mat, count);
  const o = new Object3D();
  for (let i = 0; i < count; i++) {
    const a = (i / count) * Math.PI * 2;
    o.position.set(x0 + len / 2, y + Math.cos(a) * r, z + Math.sin(a) * r);
    o.rotation.set(a, 0, 0);
    o.updateMatrix();
    inst.setMatrixAt(i, o.matrix);
  }
  g.add(inst);
}

/* ---------- Modular tank ---------- */
// Sizes are in modules (1 modül = 1,08 m); half modules allowed. Mirrors svg_tank() in app/models.php.
const cellsOf = (a) => { // full cells, then a half one at the end
  const n = Math.floor(a + 1e-9);
  const out = [];
  for (let i = 0; i < n; i++) out.push([i, 1]);
  if (a - n > 1e-9) out.push([n, a - n]);
  return out;
};
const seamsOf = (a) => [0, ...cellsOf(a).map(([s, z]) => s + z)];

function panelGeometry(variant) {
  const g = new PlaneGeometry(0.965, 0.965, 36, 36);
  if (variant !== 'sandvic') {
    // Pressed panel: a rounded-square boss with a flat top and a stiffening rim
    const p = g.attributes.position;
    for (let i = 0; i < p.count; i++) {
      const x = p.getX(i), y = p.getY(i);
      const d = Math.pow(Math.abs(x) ** 4 + Math.abs(y) ** 4, 0.25);
      const t = MathUtils.clamp((0.35 - d) / 0.09, 0, 1);
      let z = 0.042 * t * t * (3 - 2 * t);
      if (Math.max(Math.abs(x), Math.abs(y)) > 0.44) z += 0.012;
      p.setZ(i, z);
    }
    g.computeVertexNormals();
  }
  return g;
}

function buildTank(variant = 'galvaniz', W = 4, L = 3, H = 2) {
  const spec = TANK[variant] || TANK.galvaniz;
  const g = new Group();
  const baseH = 0.14;
  const mat = new MeshStandardMaterial({ color: spec.color, metalness: spec.metalness, roughness: spec.roughness, side: DoubleSide });
  const ribMat = new MeshStandardMaterial({ color: spec.rib, metalness: spec.metalness * 0.8, roughness: spec.roughness + 0.1 });
  const geo = panelGeometry(variant);
  // rows from the base (y up): full rows first, the half row (if any) on top
  const rows = cellsOf(H);

  const walls = []; // [x, y, z, rotY, rotX, scaleU, scaleV]
  for (const [x0, cw] of cellsOf(W)) for (const [y0, ch] of rows) {
    const x = x0 + cw / 2 - W / 2, y = baseH + y0 + ch / 2;
    walls.push([x, y, L / 2, 0, 0, cw, ch], [x, y, -L / 2, Math.PI, 0, cw, ch]);
  }
  for (const [z0, cl] of cellsOf(L)) for (const [y0, ch] of rows) {
    const z = z0 + cl / 2 - L / 2, y = baseH + y0 + ch / 2;
    walls.push([W / 2, y, z, Math.PI / 2, 0, cl, ch], [-W / 2, y, z, -Math.PI / 2, 0, cl, ch]);
  }
  for (const [x0, cw] of cellsOf(W)) for (const [z0, cl] of cellsOf(L)) {
    walls.push([x0 + cw / 2 - W / 2, baseH + H, z0 + cl / 2 - L / 2, 0, -Math.PI / 2, cw, cl]);
  }
  const panels = new InstancedMesh(geo, mat, walls.length);
  const o = new Object3D();
  walls.forEach(([x, y, z, ry, rx, su, sv], idx) => {
    o.position.set(x, y, z);
    o.rotation.set(rx, ry, 0, 'YXZ');
    o.scale.set(su, sv, 1);
    o.updateMatrix();
    panels.setMatrixAt(idx, o.matrix);
  });
  o.scale.set(1, 1, 1);
  g.add(panels);

  // Inner shell visible through panel seams
  g.add(box(W - 0.03, H - 0.02, L - 0.03, M.dark(), 0, baseH + 0.01, 0));

  // External flanges along seams
  const ribs = [];
  for (const x of seamsOf(W)) { ribs.push([x - W / 2, baseH + H / 2, L / 2 + 0.015, 0.035, H, 0.03], [x - W / 2, baseH + H / 2, -L / 2 - 0.015, 0.035, H, 0.03]); }
  for (const z of seamsOf(L)) { ribs.push([W / 2 + 0.015, baseH + H / 2, z - L / 2, 0.03, H, 0.035], [-W / 2 - 0.015, baseH + H / 2, z - L / 2, 0.03, H, 0.035]); }
  for (const y of [0, ...rows.map(([s, z]) => s + z)]) {
    ribs.push([0, baseH + y, L / 2 + 0.015, W, 0.035, 0.03], [0, baseH + y, -L / 2 - 0.015, W, 0.035, 0.03]);
    ribs.push([W / 2 + 0.015, baseH + y, 0, 0.03, 0.035, L], [-W / 2 - 0.015, baseH + y, 0, 0.03, 0.035, L]);
  }
  const ribInst = new InstancedMesh(new BoxGeometry(1, 1, 1), ribMat, ribs.length);
  ribs.forEach(([x, y, z, sx, sy, sz], idx) => {
    o.position.set(x, y, z); o.rotation.set(0, 0, 0); o.scale.set(sx, sy, sz); o.updateMatrix();
    ribInst.setMatrixAt(idx, o.matrix);
  });
  o.scale.set(1, 1, 1);
  g.add(ribInst);

  // Base: steel beams under every seam
  const frame = M.frame();
  for (const x of seamsOf(W)) g.add(box(0.1, baseH, L + 0.2, frame, x - W / 2, 0, 0));

  // Roof manhole (back corner) + vent (front corner)
  const top = baseH + H;
  const mx = -W / 2 + Math.min(0.62, W / 2), mz = -L / 2 + Math.min(0.62, L / 2);
  g.add(cylY(0.3, 0.06, ribMat, mx, top + 0.02, mz), cylY(0.24, 0.02, M.frame(), mx, top + 0.08, mz));
  if (W + L >= 3) {
    g.add(cylY(0.05, 0.25, M.steelDark(), W / 2 - 0.45, top, L / 2 - 0.45));
    g.add(mesh(new SphereGeometry(0.1, 20, 12, 0, Math.PI * 2, 0, Math.PI / 2), M.steelDark(), W / 2 - 0.45, top + 0.25, L / 2 - 0.45));
  }

  // Right face (+x): level gauge near the front, ladder towards the back (rails continue above the roof)
  const glass = new MeshStandardMaterial({ color: 0xffffff, transparent: true, opacity: 0.35, roughness: 0.05 });
  const gh = Math.max(0.12, H - 0.35);
  g.add(cylY(0.035, gh, glass, W / 2 + 0.08, baseH + 0.15, L / 2 - 0.3));
  g.add(cylY(0.022, gh * 0.7, M.water(), W / 2 + 0.08, baseH + 0.15, L / 2 - 0.3));
  const lz = L / 2 - Math.max(0.55, L - 0.75) - 0.17, lx = W / 2 + 0.12;
  const rail = M.steelDark();
  g.add(cylY(0.02, H + baseH + 0.42, rail, lx, 0, lz - 0.17), cylY(0.02, H + baseH + 0.42, rail, lx, 0, lz + 0.17));
  for (let y = baseH + 0.22; y < top + 0.35; y += 0.26) g.add(cylZ(0.015, 0.34, rail, lx, y, lz));
  const hand = mesh(new TorusGeometry(0.17, 0.02, 8, 24, Math.PI), rail, lx, top + 0.42, lz);
  hand.rotation.y = Math.PI / 2;
  g.add(hand);

  // Front face (+z): inlet near the top, outlet near the bottom
  const pipe = M.steelDark();
  const nozzle = (x, y, r) => {
    g.add(cylZ(r, 0.34, pipe, x, y, L / 2 + 0.17));
    g.add(cylZ(r * 1.5, 0.03, M.frame(), x, y, L / 2 + 0.02), cylZ(r * 1.45, 0.03, M.frame(), x, y, L / 2 + 0.34));
  };
  nozzle(-W / 2 + 0.5, baseH + H - Math.min(0.4, H * 0.35), 0.075);
  if (W >= 1.5 || H >= 1) nozzle(-W / 2 + Math.min(1.5, W - 0.5), baseH + Math.min(0.35, H * 0.35), 0.09);
  return g;
}

/* ---------- Pumps ---------- */
function verticalPump(scale = 1) {
  const g = new Group();
  const steel = M.steel(), dark = M.frame(), paint = M.paint();
  g.add(box(0.34, 0.06, 0.28, dark, 0, 0, 0));
  // inline suction/discharge chamber with flanges
  g.add(box(0.3, 0.12, 0.16, M.steelDark(), 0, 0.06, 0));
  g.add(cylX(0.055, 0.5, steel, 0, 0.14, 0));
  g.add(cylX(0.085, 0.025, dark, -0.26, 0.14, 0), cylX(0.085, 0.025, dark, 0.26, 0.14, 0));
  // stage sleeve with rings
  g.add(cylY(0.09, 0.55, steel, 0, 0.18, 0));
  for (let i = 0; i < 6; i++) {
    const ring = mesh(new TorusGeometry(0.091, 0.006, 8, 40), M.steelDark(), 0, 0.24 + i * 0.085, 0);
    ring.rotation.x = Math.PI / 2;
    g.add(ring);
  }
  // motor stool + motor
  g.add(cylY(0.12, 0.05, M.steelDark(), 0, 0.73, 0));
  g.add(cylY(0.11, 0.34, paint, 0, 0.78, 0));
  finsY(g, 0.112, 0.8, 0.28, 28, paint);
  g.add(cylY(0.08, 0.06, M.dark(), 0, 1.12, 0));
  g.add(box(0.1, 0.11, 0.07, M.dark(), 0, 0.92, 0.13));
  g.scale.setScalar(scale);
  return g;
}

function buildBooster(n = 3) {
  const g = new Group();
  const gap = 0.48;
  const x0 = -((n - 1) * gap) / 2 - (n === 1 ? 0.25 : 0);
  const frame = M.frame(), steel = M.steel();
  const len = (n - 1) * gap + 0.9 + (n === 1 ? 0.5 : 0);
  const cx = x0 + (n - 1) * gap / 2 + (n === 1 ? 0.25 : 0);
  // base frame
  g.add(box(len, 0.07, 0.07, frame, cx, 0, 0.26), box(len, 0.07, 0.07, frame, cx, 0, -0.26));
  for (let i = -1; i <= n; i++) g.add(box(0.06, 0.07, 0.58, frame, x0 + i * gap + (i === -1 ? 0.2 : 0) - (i === n ? 0.2 : 0), 0, 0));
  // manifolds: suction front-low, discharge back-high
  const mLen = len - 0.1;
  g.add(cylX(0.075, mLen, steel, cx, 0.21, 0.24), cylX(0.065, mLen, steel, cx, 0.46, -0.2));
  g.add(cylX(0.12, 0.03, frame, cx - mLen / 2, 0.21, 0.24), cylX(0.11, 0.03, frame, cx - mLen / 2, 0.46, -0.2));
  for (let i = 0; i < n; i++) {
    const x = x0 + i * gap;
    const p = verticalPump(1);
    p.position.set(x, 0.07, 0);
    g.add(p);
    // connections: suction to front manifold, discharge riser to back manifold
    g.add(cylZ(0.04, 0.2, steel, x - 0.12, 0.21, 0.13));
    g.add(cylY(0.035, 0.26, steel, x + 0.2, 0.21, 0), cylZ(0.035, 0.22, steel, x + 0.2, 0.46, -0.1));
    // isolation valve handle
    g.add(box(0.12, 0.015, 0.03, M.red(), x + 0.2, 0.36, 0.05));
  }
  // membrane tank at the discharge end
  const tx = cx + len / 2 - 0.02;
  const tank = new Group();
  tank.add(cylY(0.15, 0.36, M.red(), 0, 0.14, 0));
  tank.add(mesh(new SphereGeometry(0.15, 32, 16, 0, Math.PI * 2, 0, Math.PI / 2), M.red(), 0, 0.5, 0));
  tank.add(cylY(0.03, 0.14, M.steelDark(), 0, 0, 0));
  tank.position.set(tx + 0.12, 0.07, -0.2);
  g.add(tank);
  // control panel on a post
  const post = cylY(0.025, 1.2, frame, cx, 0.07, -0.3);
  const panel = box(0.46, 0.5, 0.18, M.white(), cx, 1.15, -0.32);
  const scr = mesh(new PlaneGeometry(0.2, 0.09), M.screen(), cx - 0.06, 1.5, -0.225);
  const led1 = mesh(new CircleGeometry(0.018, 16), M.led(0x22c55e), cx + 0.13, 1.51, -0.225);
  const led2 = mesh(new CircleGeometry(0.018, 16), M.led(0xef4444), cx + 0.13, 1.44, -0.225);
  g.add(post, panel, scr, led1, led2);
  return g;
}

function buildHorizontalPump() {
  const g = new Group();
  const frame = M.frame(), paint = M.paint(), steel = M.steel();
  g.add(box(1.5, 0.06, 0.42, frame, 0.15, 0, 0));
  // motor
  g.add(box(0.1, 0.1, 0.34, frame, 0.3, 0.06, 0), box(0.1, 0.1, 0.34, frame, 0.72, 0.06, 0));
  g.add(cylX(0.2, 0.62, paint, 0.51, 0.36, 0));
  finsX(g, 0.205, 0.22, 0.54, 36, 0.36, 0, paint);
  g.add(cylX(0.17, 0.08, M.dark(), 0.86, 0.36, 0));
  g.add(box(0.2, 0.1, 0.16, M.dark(), 0.45, 0.56, 0));
  // coupling guard + bearing bracket
  g.add(cylX(0.1, 0.22, M.steelDark(), 0.09, 0.36, 0));
  // volute casing
  const vol = mesh(new CylinderGeometry(0.26, 0.26, 0.18, 48), steel, -0.16, 0.36, 0);
  vol.rotation.z = Math.PI / 2;
  g.add(vol);
  const tor = mesh(new TorusGeometry(0.2, 0.08, 18, 48), steel, -0.16, 0.36, 0);
  tor.rotation.y = Math.PI / 2;
  g.add(tor);
  g.add(box(0.2, 0.1, 0.3, frame, -0.16, 0.06, 0));
  // suction (axial, -x) and discharge (up)
  g.add(cylX(0.1, 0.24, steel, -0.36, 0.36, 0), cylX(0.15, 0.03, frame, -0.49, 0.36, 0));
  g.add(cylY(0.08, 0.28, steel, -0.1, 0.56, 0), cylY(0.13, 0.03, frame, -0.1, 0.84, 0));
  return g;
}

function buildCirculator() {
  const g = new Group();
  const steel = M.steel();
  g.add(cylX(0.07, 1.1, steel, 0, 0.2, 0));
  g.add(cylX(0.13, 0.04, M.steelDark(), -0.42, 0.2, 0), cylX(0.13, 0.04, M.steelDark(), 0.42, 0.2, 0));
  g.add(cylY(0.17, 0.22, steel, 0, 0.1, 0));
  g.add(cylY(0.15, 0.3, M.paint(), 0, 0.32, 0));
  finsY(g, 0.152, 0.34, 0.24, 24, M.paint());
  const head = box(0.3, 0.16, 0.3, M.dark(), 0, 0.62, 0);
  g.add(head, mesh(new PlaneGeometry(0.14, 0.07), M.screen(), -0.03, 0.72, 0.151), mesh(new CircleGeometry(0.02, 16), M.led(0xef4444), 0.09, 0.72, 0.151));
  return g;
}

function buildDeepWell() {
  const g = new Group();
  const steel = M.steel();
  g.add(cylY(0.09, 0.5, M.paint(), 0, 0, 0));
  g.add(cylY(0.085, 0.12, M.dark(), 0, 0.5, 0));
  for (let i = 0; i < 8; i++) {
    const a = (i / 8) * Math.PI * 2;
    g.add(box(0.02, 0.1, 0.02, M.steelDark(), Math.cos(a) * 0.087, 0.51, Math.sin(a) * 0.087));
  }
  g.add(cylY(0.09, 0.95, steel, 0, 0.62, 0));
  for (let i = 0; i < 8; i++) {
    const r = mesh(new TorusGeometry(0.091, 0.006, 8, 40), M.steelDark(), 0, 0.7 + i * 0.11, 0);
    r.rotation.x = Math.PI / 2;
    g.add(r);
  }
  g.add(cylY(0.1, 0.06, M.steelDark(), 0, 1.57, 0), cylY(0.05, 0.25, steel, 0, 1.63, 0));
  const cable = new CatmullRomCurve3([new Vector3(0.095, 0.2, 0), new Vector3(0.105, 0.9, 0), new Vector3(0.12, 1.6, 0.02), new Vector3(0.2, 2.0, 0.1)]);
  g.add(new Mesh(new TubeGeometry(cable, 40, 0.012, 8), M.dark()));
  return g;
}

function buildDrain() {
  const g = new Group();
  const steel = M.steel();
  g.add(cylY(0.2, 0.16, M.steelDark(), 0, 0, 0));
  for (let i = 0; i < 18; i++) {
    const a = (i / 18) * Math.PI * 2;
    g.add(box(0.03, 0.1, 0.01, M.dark(), Math.cos(a) * 0.2, 0.03, Math.sin(a) * 0.2).rotateY(-a));
  }
  g.add(cylY(0.17, 0.42, steel, 0, 0.16, 0));
  g.add(cylY(0.12, 0.05, M.steelDark(), 0, 0.58, 0));
  const handle = mesh(new TorusGeometry(0.1, 0.015, 10, 30, Math.PI), M.dark(), 0, 0.63, 0);
  g.add(handle);
  g.add(cylX(0.045, 0.24, steel, 0.24, 0.5, 0), cylX(0.07, 0.02, M.frame(), 0.36, 0.5, 0));
  const cable = new CatmullRomCurve3([new Vector3(-0.1, 0.62, 0), new Vector3(-0.3, 0.75, 0.05), new Vector3(-0.42, 0.55, 0.12), new Vector3(-0.48, 0.42, 0.14)]);
  g.add(new Mesh(new TubeGeometry(cable, 30, 0.01, 8), M.dark()));
  const fl = mesh(new SphereGeometry(0.05, 20, 12), M.red(), -0.5, 0.38, 0.15);
  fl.scale.set(1, 0.7, 1.6);
  g.add(fl);
  return g;
}

function build(spec, dims = {}) {
  const [type, variant] = String(spec || 'tank:galvaniz').split(':');
  switch (type) {
    case 'tank': return buildTank(variant, dims.w || 4, dims.l || 3, dims.h || 2);
    case 'booster': return buildBooster(Math.max(1, Math.min(4, parseInt(variant, 10) || 3)));
    case 'pump': return variant === 'vertical' ? verticalPump(1) : variant === 'circulator' ? buildCirculator() : buildHorizontalPump();
    case 'sub': return variant === 'drain' ? buildDrain() : buildDeepWell();
    default: return buildTank('galvaniz', 4, 3, 2);
  }
}

/* ---------- Scene ---------- */
function groundTexture() {
  const c = document.createElement('canvas');
  c.width = c.height = 256;
  const x = c.getContext('2d');
  const grd = x.createRadialGradient(128, 128, 10, 128, 128, 128);
  grd.addColorStop(0, 'rgba(19,36,61,0.35)');
  grd.addColorStop(1, 'rgba(19,36,61,0)');
  x.fillStyle = grd;
  x.fillRect(0, 0, 256, 256);
  const t = new CanvasTexture(c);
  t.colorSpace = SRGBColorSpace;
  return t;
}

function disposeGroup(obj) {
  obj.traverse((o) => {
    if (o.geometry) o.geometry.dispose();
    if (o.material) (Array.isArray(o.material) ? o.material : [o.material]).forEach((m) => { if (m.map) m.map.dispose(); m.dispose(); });
  });
}

export function mount(container, spec, dims = {}, opts = {}) {
  const renderer = new WebGLRenderer({ antialias: true, alpha: true });
  renderer.setPixelRatio(Math.min(window.devicePixelRatio || 1, 2));
  renderer.outputColorSpace = SRGBColorSpace;
  renderer.toneMapping = ACESFilmicToneMapping;
  renderer.toneMappingExposure = 1.05;
  container.appendChild(renderer.domElement);

  const scene = new Scene();
  if (opts.background) scene.background = new Color(opts.background);
  const pmrem = new PMREMGenerator(renderer);
  scene.environment = pmrem.fromScene(new RoomEnvironment(), 0.04).texture;
  scene.add(new HemisphereLight(0xffffff, 0x445566, 0.6));
  const sun = new DirectionalLight(0xffffff, 1.6);
  sun.position.set(4, 8, 5);
  scene.add(sun);

  const camera = new PerspectiveCamera(32, 1, 0.05, 200);
  const controls = new OrbitControls(camera, renderer.domElement);
  controls.enableDamping = true;
  controls.enablePan = false;
  controls.maxPolarAngle = MathUtils.degToRad(86);
  controls.autoRotate = !window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  controls.autoRotateSpeed = 1.1;
  controls.addEventListener('start', () => { controls.autoRotate = false; });

  const ground = mesh(new PlaneGeometry(1, 1), new MeshBasicMaterial({ map: groundTexture(), transparent: true, depthWrite: false }));
  ground.rotation.x = -Math.PI / 2;
  scene.add(ground);

  let model = null;
  let firstFit = true;
  let current = [spec, dims];
  function setModel(s, d) {
    current = [s, d];
    if (model) { scene.remove(model); disposeGroup(model); }
    model = build(s, d);
    scene.add(model);
    const bb = new Box3().setFromObject(model);
    const size = bb.getSize(new Vector3());
    const center = bb.getCenter(new Vector3());
    model.position.x -= center.x;
    model.position.z -= center.z;
    const span = Math.max(size.x, size.z);
    ground.scale.set(span * 1.8, span * 1.8, 1);
    const vFov = MathUtils.degToRad(camera.fov / 2);
    const hFov = Math.atan(Math.tan(vFov) * Math.max(camera.aspect, 0.5));
    const footprint = Math.hypot(size.x, size.z);
    const dist = Math.max((size.y * 0.62) / Math.tan(vFov), (footprint * 0.55) / Math.tan(hFov)) + footprint * 0.35;
    controls.target.set(0, size.y * 0.5, 0);
    controls.minDistance = dist * 0.45;
    controls.maxDistance = dist * 2.2;
    if (firstFit) {
      const dir = new Vector3(1.1, 0.75, 1.3).normalize();
      camera.position.copy(controls.target).addScaledVector(dir, dist);
      firstFit = false;
    } else {
      const dir = camera.position.clone().sub(controls.target).normalize();
      camera.position.copy(controls.target).addScaledVector(dir, dist);
    }
    camera.near = dist / 100;
    camera.far = dist * 10;
    camera.updateProjectionMatrix();
  }
  setModel(spec, dims);

  function resize() {
    const w = container.clientWidth, h = container.clientHeight || w * 0.6;
    renderer.setSize(w, h, false);
    renderer.domElement.style.width = '100%';
    renderer.domElement.style.height = '100%';
    const was = camera.aspect;
    camera.aspect = w / h;
    camera.updateProjectionMatrix();
    if (model && Math.abs(was - camera.aspect) > 0.05) setModel(current[0], current[1]);
  }
  const ro = new ResizeObserver(resize);
  ro.observe(container);
  resize();

  let running = true;
  let visible = true;
  const io = new IntersectionObserver(([e]) => { visible = e.isIntersecting; });
  io.observe(container);
  renderer.setAnimationLoop(() => {
    if (!running || !visible || document.hidden) return;
    controls.update();
    renderer.render(scene, camera);
  });

  return {
    update(s, d) { setModel(s, d); },
    dispose() {
      running = false;
      renderer.setAnimationLoop(null);
      ro.disconnect(); io.disconnect();
      controls.dispose();
      if (model) disposeGroup(model);
      disposeGroup(ground);
      scene.environment.dispose();
      pmrem.dispose();
      renderer.dispose();
      renderer.domElement.remove();
    },
  };
}

export function webglAvailable() {
  try {
    const c = document.createElement('canvas');
    return !!(window.WebGLRenderingContext && (c.getContext('webgl2') || c.getContext('webgl')));
  } catch (e) {
    return false;
  }
}
