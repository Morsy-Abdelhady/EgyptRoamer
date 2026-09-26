/* Lightweight canvas particle fields for the journey:
   - "sand":    wind-blown grains drifting sideways across the desert
   - "bubbles": rising bubbles + suspended specks in the Red Sea
   Each field only animates while `running` is true. */

const rand = (a, b) => a + Math.random() * (b - a);

export class ParticleField {
  constructor(canvas, type) {
    this.canvas = canvas;
    this.ctx = canvas.getContext("2d");
    this.type = type;
    this.running = false;
    this.parts = [];
    this.t = 0;
    this.resize = this.resize.bind(this);
    this.frame = this.frame.bind(this);
    this.resize();
    window.addEventListener("resize", this.resize);
  }

  resize() {
    const dpr = Math.min(window.devicePixelRatio || 1, 1.5);
    const w = this.canvas.clientWidth || window.innerWidth;
    const h = this.canvas.clientHeight || window.innerHeight;
    this.w = w;
    this.h = h;
    this.canvas.width = Math.round(w * dpr);
    this.canvas.height = Math.round(h * dpr);
    this.ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    const area = w * h;
    const n = this.type === "sand" ? Math.min(170, area / 8500) : Math.min(120, area / 12000);
    this.parts = Array.from({ length: Math.round(n) }, () => this.spawn(true));
  }

  spawn(anywhere = false) {
    const { w, h } = this;
    if (this.type === "sand") {
      return {
        x: anywhere ? rand(0, w) : w + rand(0, 80),
        y: rand(h * 0.35, h),
        vx: -rand(0.8, 2.6),
        vy: rand(-0.15, 0.15),
        s: rand(0.4, 1.5),
        a: rand(0.12, 0.45),
        warm: Math.random() > 0.35,
      };
    }
    const speck = Math.random() > 0.55;
    return {
      x: rand(0, w),
      y: anywhere ? rand(0, h) : h + rand(0, 60),
      vy: speck ? -rand(0.05, 0.2) : -rand(0.35, 1.1),
      r: speck ? rand(0.4, 1.1) : rand(1, 3.8),
      a: speck ? rand(0.15, 0.4) : rand(0.25, 0.6),
      ph: rand(0, Math.PI * 2),
      speck,
    };
  }

  start() {
    if (this.running) return;
    this.running = true;
    requestAnimationFrame(this.frame);
  }
  stop() {
    this.running = false;
  }

  frame() {
    if (!this.running) return;
    const { ctx, w, h } = this;
    this.t += 0.016;
    ctx.clearRect(0, 0, w, h);

    if (this.type === "sand") {
      const gust = 1 + Math.sin(this.t * 0.7) * 0.45 + Math.sin(this.t * 2.3) * 0.15;
      ctx.lineCap = "round";
      for (const p of this.parts) {
        p.x += p.vx * gust;
        p.y += p.vy + Math.sin(this.t * 2 + p.x * 0.01) * 0.12;
        if (p.x < -20) Object.assign(p, this.spawn());
        ctx.strokeStyle = p.warm ? `rgba(232, 200, 150, ${p.a})` : `rgba(255, 255, 255, ${p.a * 0.8})`;
        ctx.lineWidth = p.s;
        ctx.beginPath();
        ctx.moveTo(p.x, p.y);
        ctx.lineTo(p.x - p.vx * gust * 5, p.y - p.vy * 5);
        ctx.stroke();
      }
    } else {
      for (const p of this.parts) {
        p.y += p.vy;
        p.ph += 0.02;
        const x = p.x + Math.sin(p.ph) * (p.speck ? 3 : 6);
        if (p.y < -10) Object.assign(p, this.spawn());
        if (p.speck) {
          ctx.fillStyle = `rgba(200, 240, 245, ${p.a})`;
          ctx.fillRect(x, p.y, p.r, p.r);
        } else {
          ctx.strokeStyle = `rgba(255, 255, 255, ${p.a})`;
          ctx.lineWidth = 0.8;
          ctx.beginPath();
          ctx.arc(x, p.y, p.r, 0, Math.PI * 2);
          ctx.stroke();
          ctx.fillStyle = `rgba(255, 255, 255, ${p.a * 0.6})`;
          ctx.beginPath();
          ctx.arc(x - p.r * 0.35, p.y - p.r * 0.35, p.r * 0.3, 0, Math.PI * 2);
          ctx.fill();
        }
      }
    }
    requestAnimationFrame(this.frame);
  }
}
