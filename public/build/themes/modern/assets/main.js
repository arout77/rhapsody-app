const magneticButton = {
  init() {
    document.querySelectorAll(".magnetic-button").forEach((button) => {
      const gradients = button.querySelectorAll(".magnetic-button-gradient");
      if (gradients.length === 0) return;
      const gradientHalf = 41;
      const moveGradient = gsap.quickTo(gradients, "x", {
        duration: 0.75,
        ease: "power2.out"
      });
      button.addEventListener("mousemove", (e) => {
        const rect = button.getBoundingClientRect();
        const mouseX = e.clientX - rect.left;
        moveGradient(mouseX - rect.width + gradientHalf);
      });
      button.addEventListener("mouseleave", () => {
        moveGradient(0);
      });
    });
  }
};
document.addEventListener("DOMContentLoaded", () => {
  magneticButton.init();
});
const commonAnimation = {
  init() {
    gsap.registerPlugin(ScrollTrigger);
    const imagesLoadingReveal = document.querySelectorAll(".image-loading-reveal img");
    const stepLine = document.querySelectorAll(".step-line");
    const progressBars = document.querySelectorAll(
      ".progress-bar-blue, .progress-bar-black, .progress-bar-lemon, .progress-bar-cyan"
    );
    if (stepLine.length > 0) {
      gsap.set(stepLine, { height: "0px" });
      const firstStepLine = stepLine[0];
      const triggerElement = firstStepLine.closest(".step-line-container") || firstStepLine.parentElement || firstStepLine;
      const stepTimeline = gsap.timeline({
        scrollTrigger: {
          trigger: triggerElement,
          start: "top 79%",
          end: "top 15%",
          toggleActions: "play none none"
        }
      });
      stepLine.forEach((line, index) => {
        stepTimeline.to(
          line,
          {
            height: "100%",
            duration: 1.4,
            ease: "power3.out"
          },
          index * 0.7
          // Each animation with 0.7s delay duration)
        );
      });
    }
    if (progressBars.length > 0) {
      gsap.set(progressBars, { height: "0px" });
      const progressTimeline = gsap.timeline({
        scrollTrigger: {
          trigger: ".progress-bar-blue",
          start: "top 88%",
          end: "bottom 20%",
          toggleActions: "play none none "
        }
      });
      progressTimeline.to(
        ".progress-bar-blue",
        {
          height: "30%",
          duration: 1.2,
          ease: "power2.out"
        },
        "-=0.3"
      ).to(
        ".progress-bar-black",
        {
          height: "65%",
          duration: 1.2,
          ease: "power2.out"
        },
        "-=0.3"
      ).to(
        ".progress-bar-lemon",
        {
          height: "45%",
          duration: 1.2,
          ease: "power2.out"
        },
        "-=0.8"
      ).to(
        ".progress-bar-cyan",
        {
          height: "30%",
          duration: 1.2,
          ease: "power2.out"
        },
        "-=0.9"
      );
    }
    if (imagesLoadingReveal.length > 0) {
      imagesLoadingReveal.forEach((img) => {
        const triggerEl = img.closest(".image-loading-reveal") || img;
        gsap.set(img, {
          clipPath: "polygon(0% 0%, 100% 0%, 100% 0%, 0% 0%)",
          opacity: 0,
          scale: 0.9
        });
        gsap.fromTo(
          img,
          {
            clipPath: "polygon(0% 0%, 100% 0%, 100% 0%, 0% 0%)",
            opacity: 0,
            scale: 0.9
          },
          {
            clipPath: "polygon(0% 0%, 100% 0%, 100% 100%, 0% 100%)",
            opacity: 1,
            scale: 1,
            ease: "back.in",
            duration: 0.9,
            scrollTrigger: {
              trigger: triggerEl,
              start: "top 94%",
              end: "bottom 58%",
              toggleActions: "play none none none"
            }
          }
        );
      });
    }
  }
};
const updateFooterYear = () => {
  const footerYearElements = document.querySelectorAll("[data-footer-year]");
  if (footerYearElements.length > 0) {
    const currentYear = (/* @__PURE__ */ new Date()).getFullYear();
    footerYearElements.forEach((element) => {
      element.textContent = currentYear;
    });
  }
};
if (typeof window !== "undefined") {
  commonAnimation.init();
  updateFooterYear();
}
const initCounterNumberOnScroll = () => {
  if (typeof gsap === "undefined" || typeof ScrollTrigger === "undefined") return;
  gsap.registerPlugin(ScrollTrigger);
  const counterTriggers = document.querySelectorAll("[data-counter-trigger]");
  counterTriggers.forEach((counterTrigger) => {
    const counterFlow = counterTrigger.querySelector("[data-counter-number]");
    const counterValue = Number(counterTrigger.dataset.counterValue) || 0;
    const counterDuration = Number(counterTrigger.dataset.counterDuration) || 1.8;
    const counterFractionDigits = Number(counterTrigger.dataset.counterFractionDigits) || 0;
    if (!counterFlow || typeof counterFlow.update !== "function") return;
    counterFlow.trend = 0;
    counterFlow.format = {
      useGrouping: true,
      maximumFractionDigits: counterFractionDigits,
      minimumFractionDigits: counterFractionDigits
    };
    counterFlow.update(0);
    ScrollTrigger.create({
      trigger: counterTrigger,
      start: "top 90%",
      once: true,
      onEnter: () => {
        counterFlow.transformTiming = { duration: counterDuration * 1e3, easing: "ease-out" };
        counterFlow.spinTiming = { duration: counterDuration * 1e3, easing: "ease-out" };
        counterFlow.opacityTiming = {
          duration: Math.max(250, counterDuration * 450),
          easing: "ease-out"
        };
        counterFlow.update(counterValue);
      }
    });
  });
};
const bootCounterNumberOnScroll = () => {
  if (customElements.get("number-flow")) {
    initCounterNumberOnScroll();
    return;
  }
  customElements.whenDefined("number-flow").then(initCounterNumberOnScroll);
};
document.addEventListener("DOMContentLoaded", bootCounterNumberOnScroll);
const CtaAnimation = {
  initGlobeImageAnimation() {
    const globeImage = document.querySelector(".globe-image");
    gsap.from(globeImage, {
      opacity: 0,
      y: 50,
      duration: 1,
      ease: "power3.inOut",
      scrollTrigger: {
        trigger: globeImage,
        start: "top 80%",
        end: "bottom 20%"
      }
    });
  },
  initFancySlider() {
    const slider = document.getElementById("fancy-slider");
    if (slider) {
      let updateSlides2 = function() {
        slides.forEach((slide, i) => {
          const offset = (i - index + slides.length) % slides.length;
          slide.style.zIndex = 0;
          slide.style.border = "4px solid";
          slide.style.borderRadius = "8px";
          slide.style.borderColor = "var(--color-background-1)";
          slide.style.filter = "blur(4px)";
          slide.style.transform = "scale(0.7) translateX(0) rotate(0deg)";
          slide.style.width = "180px";
          slide.style.height = "180px";
          if (offset === 0) {
            slide.style.zIndex = 10;
            slide.style.filter = "blur(0)";
            slide.style.transform = "scale(1) translateX(0) rotate(0)";
            slide.style.border = "4px solid";
            slide.style.boxShadow = "0 0 40px #000";
            slide.style.borderColor = "white";
            slide.style.borderRadius = "11px";
            slide.style.width = "200px";
            slide.style.height = "200px";
          } else if (offset === 1 || offset === slides.length - 1) {
            slide.style.zIndex = 5;
            slide.style.transform = `scale(1) translateX(${offset === 1 ? 185 : -185}px) translateY(40px) rotate(${offset === 1 ? "20deg" : "-20deg"})`;
          } else {
            slide.style.zIndex = 1;
            slide.style.transform = `scale(0.87) translateX(${offset === 2 ? 350 : -350}px) translateY(140px) rotate(${offset === 2 ? "50deg" : "-50deg"}) opacity(0)`;
          }
        });
      }, nextSlide2 = function() {
        index = (index + 1) % slides.length;
        updateSlides2();
      };
      var updateSlides = updateSlides2, nextSlide = nextSlide2;
      const slides = slider.querySelectorAll("figure");
      let index = 0;
      updateSlides2();
      setInterval(nextSlide2, 2500);
    }
  }
};
document.addEventListener("DOMContentLoaded", () => {
  CtaAnimation.initGlobeImageAnimation();
  CtaAnimation.initFancySlider();
});
const glowingCardsAnimation = {
  init() {
    const wrappers = document.querySelectorAll(".glowing-cards");
    wrappers.forEach((wrapper) => {
      const cards = wrapper.querySelectorAll(".glowing-card, .glowing-card-v2");
      let isMouseInside = false;
      wrapper.addEventListener("mouseenter", (event) => {
        isMouseInside = true;
        cards.forEach((card) => {
          const rect = card.getBoundingClientRect();
          const x = event.clientX - rect.left;
          const y = event.clientY - rect.top;
          card.style.setProperty("--xPos", `${x}px`);
          card.style.setProperty("--yPos", `${y}px`);
        });
      });
      wrapper.addEventListener("mousemove", (event) => {
        if (!isMouseInside) return;
        cards.forEach((card) => {
          const rect = card.getBoundingClientRect();
          const x = event.clientX - rect.left;
          const y = event.clientY - rect.top;
          card.style.setProperty("--xPos", `${x}px`);
          card.style.setProperty("--yPos", `${y}px`);
        });
      });
      wrapper.addEventListener("mouseleave", () => {
        isMouseInside = false;
      });
    });
  }
};
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    glowingCardsAnimation.init();
  });
} else {
  glowingCardsAnimation.init();
}
const CLOSE_DELAY = 120;
const MENU_DURATION = 0.5;
const ITEM_STAGGER = 0.025;
function initHeaderMenus() {
  const header = document.querySelector("header");
  const nav = header == null ? void 0 : header.querySelector("nav");
  const root = nav == null ? void 0 : nav.querySelector("[data-dropdown-root]");
  const container = root == null ? void 0 : root.querySelector("[data-dropdown-container]");
  if (!header || !nav || !root || !container) return;
  const triggers = Array.from(nav.querySelectorAll("[data-menu]"));
  const sectionEls = Array.from(container.querySelectorAll("[data-dropdown-section]"));
  if (!triggers.length || !sectionEls.length) return;
  const sections = /* @__PURE__ */ new Map();
  sectionEls.forEach((section) => {
    const name = section.dataset.dropdownSection;
    if (!name) return;
    sections.set(name, { section });
  });
  let activeName = null;
  let activeTrigger = null;
  let activeTween = null;
  let closeTimer = null;
  const gsap2 = globalThis.gsap;
  const getMenuLayout = (trigger, section) => {
    const navRect = nav.getBoundingClientRect();
    const triggerRect = trigger.getBoundingClientRect();
    const triggerCenter = triggerRect.left + triggerRect.width / 2 - navRect.left;
    return {
      left: triggerCenter,
      width: section.offsetWidth,
      height: section.offsetHeight
    };
  };
  const setMenu = (name, trigger) => {
    const target = sections.get(name);
    if (!target) return;
    const layout = getMenuLayout(trigger, target.section);
    root.style.left = `${layout.left}px`;
    root.style.width = `${layout.width}px`;
    container.style.width = `${layout.width}px`;
    container.style.height = `${layout.height}px`;
    sections.forEach((entry, key) => {
      const isActive = key === name;
      entry.section.classList.toggle("opacity-100", isActive);
      entry.section.classList.toggle("opacity-0", !isActive);
      entry.section.classList.toggle("pointer-events-auto", isActive);
      entry.section.classList.toggle("pointer-events-none", !isActive);
    });
  };
  const animateMenu = (name, trigger, shouldAnimateItems, wasClosed) => {
    const target = sections.get(name);
    if (!target) return;
    if (!gsap2) {
      setMenu(name, trigger);
      return;
    }
    const layout = getMenuLayout(trigger, target.section);
    const items = target.section.querySelectorAll("a");
    const inactiveSections = sectionEls.filter((section) => section !== target.section);
    if (activeTween) activeTween.kill();
    gsap2.killTweensOf([root, container, target.section, ...inactiveSections, ...items]);
    if (wasClosed) {
      gsap2.set(root, { left: layout.left, width: layout.width, opacity: 0, y: -4 });
      gsap2.set(container, { width: layout.width, height: layout.height });
    }
    if (items.length) {
      gsap2.set(items, { transition: "none", willChange: "transform, opacity" });
    }
    root.classList.remove("opacity-0");
    target.section.classList.remove("opacity-0", "pointer-events-none");
    target.section.classList.add("opacity-100", "pointer-events-auto");
    inactiveSections.forEach((section) => {
      section.classList.remove("opacity-100", "pointer-events-auto");
      section.classList.add("opacity-0", "pointer-events-none");
    });
    activeTween = gsap2.timeline({ defaults: { ease: "power3.out", force3D: true } });
    activeTween.to(
      root,
      { left: layout.left, width: layout.width, opacity: 1, y: 0, duration: MENU_DURATION },
      0
    );
    activeTween.to(
      container,
      { width: layout.width, height: layout.height, duration: MENU_DURATION },
      0
    );
    activeTween.to(inactiveSections, { opacity: 0, duration: 0.12, overwrite: "auto" }, 0);
    activeTween.set(target.section, { opacity: 1 }, 0);
    if (items.length) {
      activeTween.fromTo(
        items,
        { x: 14, opacity: 0 },
        {
          x: 0,
          opacity: 1,
          duration: 0.4,
          stagger: ITEM_STAGGER,
          ease: "power2.out",
          overwrite: "auto",
          force3D: true,
          onComplete: () => gsap2.set(items, { clearProps: "transition,willChange,transform" })
        },
        0.05
      );
    }
  };
  const openMenu = (trigger) => {
    const name = trigger.dataset.menu;
    if (!sections.has(name)) return;
    if (activeName === name) {
      activeTrigger = trigger;
      return;
    }
    const wasClosed = !activeName;
    activeName = name;
    activeTrigger = trigger;
    root.classList.remove("pointer-events-none", "opacity-0");
    root.classList.add("pointer-events-auto", "opacity-100");
    animateMenu(name, trigger, true, wasClosed);
  };
  const closeMenu = () => {
    if (!activeName) return;
    activeName = null;
    activeTrigger = null;
    if (activeTween) activeTween.kill();
    const finishClose = () => {
      root.classList.remove("pointer-events-auto", "opacity-100");
      root.classList.add("pointer-events-none", "opacity-0");
      sections.forEach((entry) => {
        entry.section.classList.remove("opacity-100", "pointer-events-auto");
        entry.section.classList.add("opacity-0", "pointer-events-none");
      });
    };
    if (!gsap2) {
      finishClose();
      return;
    }
    activeTween = gsap2.to(root, {
      opacity: 0,
      duration: 0.16,
      ease: "power2.out",
      overwrite: "auto",
      onComplete: finishClose
    });
  };
  const stopClose = () => {
    globalThis.clearTimeout(closeTimer);
  };
  const startClose = () => {
    stopClose();
    closeTimer = globalThis.setTimeout(closeMenu, CLOSE_DELAY);
  };
  triggers.forEach((trigger) => {
    const name = trigger.dataset.menu;
    if (!sections.has(name)) return;
    trigger.addEventListener("mouseenter", () => {
      stopClose();
      openMenu(trigger);
    });
    trigger.addEventListener("mouseleave", startClose);
    trigger.addEventListener("click", (event) => {
      event.preventDefault();
      if (activeTrigger === trigger) {
        closeMenu();
      } else {
        openMenu(trigger);
      }
    });
  });
  root.addEventListener("mouseenter", stopClose);
  root.addEventListener("mouseleave", startClose);
  document.addEventListener("click", (event) => {
    if (!activeName || header.contains(event.target)) return;
    closeMenu();
  });
  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") closeMenu();
  });
  globalThis.addEventListener("resize", () => {
    if (!activeName || !activeTrigger) return;
    setMenu(activeName, activeTrigger);
  });
}
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", initHeaderMenus);
} else {
  initHeaderMenus();
}
const headerAnimation = {
  headerOne() {
    const header = document.querySelector(".header-scroll");
    if (header) {
      window.addEventListener("scroll", () => {
        if (window.scrollY > 100) {
          header.style.transition = "all 0.5s ease-in-out";
          header.classList.add("scroll-header");
        } else {
          header.classList.remove("scroll-header");
        }
      });
    }
  }
};
if (globalThis.window !== void 0) {
  headerAnimation.headerOne();
}
const animation$1 = {
  init() {
    gsap.registerPlugin(MotionPathPlugin);
    gsap.registerPlugin(ScrollTrigger);
    const integrationFeaturesPill = () => {
      const paths = document.querySelectorAll(".features-integration-pill svg path.connector");
      if (!paths.length) return;
      paths.forEach((path, i) => {
        const len = path.getTotalLength();
        gsap.set(path, { strokeDasharray: len, strokeDashoffset: len });
        gsap.to(path, {
          strokeDashoffset: 0,
          duration: 1.1,
          ease: "power3.out",
          delay: i * 0.08
        });
        const svg = path.closest("svg");
        if (!svg.querySelector("defs #glow")) {
          const defs = document.createElementNS("http://www.w3.org/2000/svg", "defs");
          defs.innerHTML = `
        <filter id="glow" x="-50%" y="-50%" width="200%" height="200%">
          <feGaussianBlur stdDeviation="1" result="b"/>
          <feMerge><feMergeNode in="b"/><feMergeNode in="SourceGraphic"/></feMerge>
        </filter>`;
          svg.prepend(defs);
        }
        const pillContainer = path.closest(".features-integration-pill");
        const dotColorAttr = (pillContainer == null ? void 0 : pillContainer.getAttribute("data-dot-color")) || "white";
        const dotColor = dotColorAttr === "black" ? "#000" : "#fff";
        const dot = document.createElementNS("http://www.w3.org/2000/svg", "circle");
        dot.setAttribute("r", "1.6");
        dot.setAttribute("fill", dotColor);
        dot.setAttribute("opacity", "1");
        dot.setAttribute("filter", "url(#glow)");
        dot.style.pointerEvents = "none";
        svg.appendChild(dot);
        gsap.set(dot, { transformBox: "fill-box", transformOrigin: "50% 50%" });
        gsap.fromTo(
          dot,
          { opacity: 0 },
          {
            opacity: 1,
            duration: 2.2,
            repeat: -1,
            ease: "none",
            yoyo: false,
            // key part: align the element's CENTER to the path
            motionPath: {
              path,
              align: path,
              alignOrigin: [0.5, 0.5],
              autoRotate: false,
              start: 0,
              end: 1
            },
            delay: 0.5 + i * 0.15
          }
        );
      });
    };
    integrationFeaturesPill();
  }
};
if (document.readyState === "loading") {
  document.addEventListener("DOMContentLoaded", () => {
    animation$1.init();
  });
} else {
  animation$1.init();
}
(() => {
  if (!window.THREE) {
    console.warn("[LaserFlow] THREE not found. Load three.js before this script.");
    return;
  }
  const VERT = `
precision highp float;
attribute vec3 position;
void main(){
  gl_Position = vec4(position, 1.0);
}
`;
  const FRAG = `
#ifdef GL_ES
#extension GL_OES_standard_derivatives : enable
#endif
precision highp float;
precision mediump int;

uniform float iTime;
uniform vec3 iResolution;
uniform vec4 iMouse;
uniform float uWispDensity;
uniform float uTiltScale;
uniform float uFlowTime;
uniform float uFogTime;
uniform float uBeamXFrac;
uniform float uBeamYFrac;
uniform float uFlowSpeed;
uniform float uVLenFactor;
uniform float uHLenFactor;
uniform float uFogIntensity;
uniform float uFogScale;
uniform float uWSpeed;
uniform float uWIntensity;
uniform float uFlowStrength;
uniform float uDecay;
uniform float uFalloffStart;
uniform float uFogFallSpeed;
uniform vec3 uColor;
uniform float uFade;

#define PI 3.14159265359
#define TWO_PI 6.28318530718
#define EPS 1e-6
#define EDGE_SOFT (DT_LOCAL*4.0)
#define DT_LOCAL 0.0038
#define TAP_RADIUS 6
#define R_H 150.0
#define R_V 150.0
#define FLARE_HEIGHT 16.0
#define FLARE_AMOUNT 8.0
#define FLARE_EXP 2.0
#define TOP_FADE_START 0.1
#define TOP_FADE_EXP 1.0
#define FLOW_PERIOD 0.5
#define FLOW_SHARPNESS 1.5

#define W_BASE_X 1.5
#define W_LAYER_GAP 0.25
#define W_LANES 10
#define W_SIDE_DECAY 0.5
#define W_HALF 0.01
#define W_AA 0.15
#define W_CELL 20.0
#define W_SEG_MIN 0.01
#define W_SEG_MAX 0.55
#define W_CURVE_AMOUNT 15.0
#define W_CURVE_RANGE (FLARE_HEIGHT - 3.0)
#define W_BOTTOM_EXP 10.0

#define FOG_ON 1
#define FOG_CONTRAST 1.2
#define FOG_SPEED_U 0.1
#define FOG_SPEED_V -0.1
#define FOG_OCTAVES 5
#define FOG_BOTTOM_BIAS 0.8
#define FOG_TILT_TO_MOUSE 0.05
#define FOG_TILT_DEADZONE 0.01
#define FOG_TILT_MAX_X 0.35
#define FOG_TILT_SHAPE 1.5
#define FOG_BEAM_MIN 0.0
#define FOG_BEAM_MAX 0.75
#define FOG_MASK_GAMMA 0.5
#define FOG_EXPAND_SHAPE 12.2
#define FOG_EDGE_MIX 0.5

#define HFOG_EDGE_START 0.20
#define HFOG_EDGE_END 0.98
#define HFOG_EDGE_GAMMA 1.4
#define HFOG_Y_RADIUS 25.0
#define HFOG_Y_SOFT 60.0

#define EDGE_X0 0.22
#define EDGE_X1 0.995
#define EDGE_X_GAMMA 1.25
#define EDGE_LUMA_T0 0.0
#define EDGE_LUMA_T1 2.0
#define DITHER_STRENGTH 1.0

float g(float x){return x<=0.00031308?12.92*x:1.055*pow(x,1.0/2.4)-0.055;}
float bs(vec2 p,vec2 q,float powr){
  float d=distance(p,q),f=powr*uFalloffStart,r=(f*f)/(d*d+EPS);
  return powr*min(1.0,r);
}
float bsa(vec2 p,vec2 q,float powr,vec2 s){
  vec2 d=p-q; float dd=(d.x*d.x)/(s.x*s.x)+(d.y*d.y)/(s.y*s.y),f=powr*uFalloffStart,r=(f*f)/(dd+EPS);
  return powr*min(1.0,r);
}
float tri01(float x){float f=fract(x);return 1.0-abs(f*2.0-1.0);}
float tauWf(float t,float tmin,float tmax){float a=smoothstep(tmin,tmin+EDGE_SOFT,t),b=1.0-smoothstep(tmax-EDGE_SOFT,tmax,t);return max(0.0,a*b);} 
float h21(vec2 p){p=fract(p*vec2(123.34,456.21));p+=dot(p,p+34.123);return fract(p.x*p.y);}
float vnoise(vec2 p){
  vec2 i=floor(p),f=fract(p);
  float a=h21(i),b=h21(i+vec2(1,0)),c=h21(i+vec2(0,1)),d=h21(i+vec2(1,1));
  vec2 u=f*f*(3.0-2.0*f);
  return mix(mix(a,b,u.x),mix(c,d,u.x),u.y);
}
float fbm2(vec2 p){
  float v=0.0,amp=0.6; mat2 m=mat2(0.86,0.5,-0.5,0.86);
  for(int i=0;i<FOG_OCTAVES;++i){v+=amp*vnoise(p); p=m*p*2.03+17.1; amp*=0.52;}
  return v;
}
float rGate(float x,float l){float a=smoothstep(0.0,W_AA,x),b=1.0-smoothstep(l,l+W_AA,x);return max(0.0,a*b);}
float flareY(float y){float t=clamp(1.0-(clamp(y,0.0,FLARE_HEIGHT)/max(FLARE_HEIGHT,EPS)),0.0,1.0);return pow(t,FLARE_EXP);}

float vWisps(vec2 uv,float topF){
  float y=uv.y,yf=(y+uFlowTime*uWSpeed)/W_CELL;
  float dRaw=clamp(uWispDensity,0.0,2.0),d=dRaw<=0.0?1.0:dRaw;
  float lanesF=floor(float(W_LANES)*min(d,1.0)+0.5); 
  int lanes=int(max(1.0,lanesF));
  float sp=min(d,1.0),ep=max(d-1.0,0.0);
  float fm=flareY(max(y,0.0)),rm=clamp(1.0-(y/max(W_CURVE_RANGE,EPS)),0.0,1.0),cm=fm*rm;
  const float G=0.05; float xS=1.0+(FLARE_AMOUNT*W_CURVE_AMOUNT*G)*cm;
  float sPix=clamp(y/R_V,0.0,1.0),bGain=pow(1.0-sPix,W_BOTTOM_EXP),sum=0.0;
  for(int s=0;s<2;++s){
    float sgn=s==0?-1.0:1.0;
    for(int i=0;i<W_LANES;++i){
      if(i>=lanes) break;
      float off=W_BASE_X+float(i)*W_LAYER_GAP,xc=sgn*(off*xS);
      float dx=abs(uv.x-xc),lat=1.0-smoothstep(W_HALF,W_HALF+W_AA,dx),amp=exp(-off*W_SIDE_DECAY);
      float seed=h21(vec2(off,sgn*17.0)),yf2=yf+seed*7.0,ci=floor(yf2),fy=fract(yf2);
      float seg=mix(W_SEG_MIN,W_SEG_MAX,h21(vec2(ci,off*2.3)));
      float spR=h21(vec2(ci,off+sgn*31.0)),seg1=rGate(fy,seg)*step(spR,sp);
      if(ep>0.0){float spR2=h21(vec2(ci*3.1+7.0,off*5.3+sgn*13.0)); float f2=fract(fy+0.5); seg1+=rGate(f2,seg*0.9)*step(spR2,ep);}
      sum+=amp*lat*seg1;
    }
  }
  float span=smoothstep(-3.0,0.0,y)*(1.0-smoothstep(R_V-6.0,R_V,y));
  return uWIntensity*sum*topF*bGain*span;
}

void mainImage(out vec4 fc,in vec2 frag){
  vec2 C=iResolution.xy*.5; float invW=1.0/max(C.x,1.0);
  float sc=512.0/iResolution.x*.4;
  vec2 uv=(frag-C)*sc,off=vec2(uBeamXFrac*iResolution.x*sc,uBeamYFrac*iResolution.y*sc);
  vec2 uvc = uv - off;
  float a=0.0,b=0.0;
  float basePhase=1.5*PI+uDecay*.5; float tauMin=basePhase-uDecay; float tauMax=basePhase;
  float cx=clamp(uvc.x/(R_H*uHLenFactor),-1.0,1.0),tH=clamp(TWO_PI-acos(cx),tauMin,tauMax);
  for(int k=-TAP_RADIUS;k<=TAP_RADIUS;++k){
    float tu=tH+float(k)*DT_LOCAL,wt=tauWf(tu,tauMin,tauMax); if(wt<=0.0) continue;
    float spd=max(abs(sin(tu)),0.02),u=clamp((basePhase-tu)/max(uDecay,EPS),0.0,1.0),env=pow(1.0-abs(u*2.0-1.0),0.8);
    vec2 p=vec2((R_H*uHLenFactor)*cos(tu),0.0);
    a+=wt*bs(uvc,p,env*spd);
  }
  float yPix=uvc.y,cy=clamp(-yPix/(R_V*uVLenFactor),-1.0,1.0),tV=clamp(TWO_PI-acos(cy),tauMin,tauMax);
  for(int k=-TAP_RADIUS;k<=TAP_RADIUS;++k){
    float tu=tV+float(k)*DT_LOCAL,wt=tauWf(tu,tauMin,tauMax); if(wt<=0.0) continue;
    float yb=(-R_V)*cos(tu),s=clamp(yb/R_V,0.0,1.0),spd=max(abs(sin(tu)),0.02);
    float env=pow(1.0-s,0.6)*spd;
    float cap=1.0-smoothstep(TOP_FADE_START,1.0,s); cap=pow(cap,TOP_FADE_EXP); env*=cap;
    float ph=s/max(FLOW_PERIOD,EPS)+uFlowTime*uFlowSpeed;
    float fl=pow(tri01(ph),FLOW_SHARPNESS);
    env*=mix(1.0-uFlowStrength,1.0,fl);
    float yp=(-R_V*uVLenFactor)*cos(tu),m=pow(smoothstep(FLARE_HEIGHT,0.0,yp),FLARE_EXP),wx=1.0+FLARE_AMOUNT*m;
    vec2 sig=vec2(wx,1.0),p=vec2(0.0,yp);
    float mask=step(0.0,yp);
    b+=wt*bsa(uvc,p,mask*env,sig);
  }
  float sPix=clamp(yPix/R_V,0.0,1.0),topA=pow(1.0-smoothstep(TOP_FADE_START,1.0,sPix),TOP_FADE_EXP);
  float L=a+b*topA;
  float w=vWisps(vec2(uvc.x,yPix),topA);
  float fog=0.0;
#if FOG_ON
  vec2 fuv=uvc*uFogScale;
  float mAct=step(1.0,length(iMouse.xy)),nx=((iMouse.x-C.x)*invW)*mAct;
  float ax = abs(nx);
  float stMag = mix(ax, pow(ax, FOG_TILT_SHAPE), 0.35);
  float st = sign(nx) * stMag * uTiltScale;
  st = clamp(st, -FOG_TILT_MAX_X, FOG_TILT_MAX_X);
  vec2 dir=normalize(vec2(st,1.0));
  fuv+=uFogTime*uFogFallSpeed*dir;
  vec2 prp=vec2(-dir.y,dir.x);
  fuv+=prp*(0.08*sin(dot(uvc,prp)*0.08+uFogTime*0.9));
  float n=fbm2(fuv+vec2(fbm2(fuv+vec2(7.3,2.1)),fbm2(fuv+vec2(-3.7,5.9)))*0.6);
  n=pow(clamp(n,0.0,1.0),FOG_CONTRAST);
  float pixW = 1.0 / max(iResolution.y, 1.0);
#ifdef GL_OES_standard_derivatives
  float wL = max(fwidth(L), pixW);
#else
  float wL = pixW;
#endif
  float m0=pow(smoothstep(FOG_BEAM_MIN - wL, FOG_BEAM_MAX + wL, L),FOG_MASK_GAMMA);
  float bm=1.0-pow(1.0-m0,FOG_EXPAND_SHAPE); bm=mix(bm*m0,bm,FOG_EDGE_MIX);
  float yP=1.0-smoothstep(HFOG_Y_RADIUS,HFOG_Y_RADIUS+HFOG_Y_SOFT,abs(yPix));
  float nxF=abs((frag.x-C.x)*invW),hE=1.0-smoothstep(HFOG_EDGE_START,HFOG_EDGE_END,nxF); hE=pow(clamp(hE,0.0,1.0),HFOG_EDGE_GAMMA);
  float hW=mix(1.0,hE,clamp(yP,0.0,1.0));
  float bBias=mix(1.0,1.0-sPix,FOG_BOTTOM_BIAS);
  float browserFogIntensity = uFogIntensity;
  browserFogIntensity *= 1.8;
  float radialFade = 1.0 - smoothstep(0.0, 0.7, length(uvc) / 120.0);
  float safariFog = n * browserFogIntensity * bBias * bm * hW * radialFade;
  fog = safariFog;
#endif
  float LF=L+fog;
  float dith=(h21(frag)-0.5)*(DITHER_STRENGTH/255.0);
  float tone=g(LF+w);
  vec3 col=tone*uColor+dith;
  float alpha=clamp(g(L+w*0.6)+dith*0.6,0.0,1.0);
  float nxE=abs((frag.x-C.x)*invW),xF=pow(clamp(1.0-smoothstep(EDGE_X0,EDGE_X1,nxE),0.0,1.0),EDGE_X_GAMMA);
  float scene=LF+max(0.0,w)*0.5,hi=smoothstep(EDGE_LUMA_T0,EDGE_LUMA_T1,scene);
  float eM=mix(xF,1.0,hi);
  col*=eM; alpha*=eM;
  col*=uFade; alpha*=uFade;
  fc=vec4(col,alpha);
}

void main(){
  vec4 fc;
  mainImage(fc, gl_FragCoord.xy);
  gl_FragColor = fc;
}
`;
  const clamp = (v, lo, hi) => Math.max(lo, Math.min(hi, v));
  const toNumber = (v, def) => {
    if (v == null || v === "" || v === "auto") return def;
    const n = Number(v);
    return Number.isFinite(n) ? n : def;
  };
  const hexToRGB = (hex) => {
    if (!hex) return { r: 1, g: 1, b: 1 };
    let c = hex.trim();
    if (c[0] === "#") c = c.slice(1);
    if (c.length === 3)
      c = c.split("").map((x) => x + x).join("");
    const n = parseInt(c, 16);
    if (Number.isNaN(n)) return { r: 1, g: 1, b: 1 };
    return { r: (n >> 16 & 255) / 255, g: (n >> 8 & 255) / 255, b: (n & 255) / 255 };
  };
  function initLaserFlow(el) {
    const color = el.getAttribute("data-color") || "#FF79C6";
    const horizontalSizing = clamp(
      toNumber(el.getAttribute("data-horizontal-sizing"), 0.5),
      0.2,
      2.5
    );
    const verticalSizing = clamp(toNumber(el.getAttribute("data-vertical-sizing"), 2), 0.5, 3);
    const wispDensity = clamp(toNumber(el.getAttribute("data-wisp-density"), 1), 0, 2);
    const wispSpeed = clamp(toNumber(el.getAttribute("data-wisp-speed"), 15), 0, 40);
    const wispIntensity = clamp(toNumber(el.getAttribute("data-wisp-intensity"), 5), 0, 10);
    const flowSpeed = clamp(toNumber(el.getAttribute("data-flow-speed"), 0.35), 0, 1);
    const flowStrength = clamp(toNumber(el.getAttribute("data-flow-strength"), 0.25), 0, 1);
    const fogIntensity = clamp(toNumber(el.getAttribute("data-fog-intensity"), 0.45), 0, 1);
    const fogScale = clamp(toNumber(el.getAttribute("data-fog-scale"), 0.3), 0.05, 1.5);
    const fogFallSpeed = clamp(toNumber(el.getAttribute("data-fog-fall-speed"), 0.6), 0, 3);
    const decay = clamp(toNumber(el.getAttribute("data-decay"), 1.1), 0.3, 3);
    const falloffStart = clamp(toNumber(el.getAttribute("data-falloff-start"), 1.2), 0.2, 4);
    const beamX = clamp(toNumber(el.getAttribute("data-beam-x"), 0.1), -0.5, 0.5);
    const beamY = clamp(toNumber(el.getAttribute("data-beam-y"), 0), -0.5, 0.5);
    const mouseSmoothTime = clamp(toNumber(el.getAttribute("data-mouse-smooth"), 0), 0, 1);
    const tiltStrength = clamp(toNumber(el.getAttribute("data-tilt-strength"), 0.01), 0, 0.2);
    const dprAttr = el.getAttribute("data-dpr");
    const pointerEvents = (el.getAttribute("data-pointer-events") || "auto").toLowerCase();
    const prefersReduced = window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
    const RM = prefersReduced ? 1e-4 : 1;
    const renderer = new THREE.WebGLRenderer({
      antialias: false,
      alpha: true,
      // transparent to show image under
      depth: false,
      stencil: false,
      powerPreference: "high-performance",
      premultipliedAlpha: false,
      preserveDrawingBuffer: false,
      failIfMajorPerformanceCaveat: false,
      logarithmicDepthBuffer: false
    });
    renderer.shadowMap.enabled = false;
    renderer.outputColorSpace = THREE.SRGBColorSpace;
    renderer.setClearColor(0, 0);
    const canvas = renderer.domElement;
    canvas.className = "laser-flow-canvas";
    canvas.style.pointerEvents = pointerEvents === "none" ? "none" : "auto";
    el.appendChild(canvas);
    const scene = new THREE.Scene();
    const camera = new THREE.OrthographicCamera(-1, 1, 1, -1, 0, 1);
    const geometry = new THREE.BufferGeometry();
    geometry.setAttribute(
      "position",
      new THREE.BufferAttribute(new Float32Array([-1, -1, 0, 3, -1, 0, -1, 3, 0]), 3)
    );
    const uniforms = {
      iTime: { value: 0 },
      iResolution: { value: new THREE.Vector3(1, 1, 1) },
      iMouse: { value: new THREE.Vector4(0, 0, 0, 0) },
      uWispDensity: { value: wispDensity },
      uTiltScale: { value: tiltStrength },
      uFlowTime: { value: 0 },
      uFogTime: { value: 0 },
      uBeamXFrac: { value: beamX },
      uBeamYFrac: { value: beamY },
      uFlowSpeed: { value: flowSpeed * RM },
      uVLenFactor: { value: verticalSizing },
      uHLenFactor: { value: horizontalSizing },
      uFogIntensity: { value: fogIntensity * (prefersReduced ? 0.3 : 1) },
      uFogScale: { value: fogScale },
      uWSpeed: { value: wispSpeed * RM },
      uWIntensity: { value: wispIntensity * (prefersReduced ? 0.6 : 1) },
      uFlowStrength: { value: flowStrength },
      uDecay: { value: decay },
      uFalloffStart: { value: falloffStart },
      uFogFallSpeed: { value: fogFallSpeed * RM },
      uColor: { value: new THREE.Vector3(1, 1, 1) },
      uFade: { value: 0 }
    };
    const { r, g, b } = hexToRGB(color);
    uniforms.uColor.value.set(r, g, b);
    const material = new THREE.RawShaderMaterial({
      vertexShader: VERT,
      fragmentShader: FRAG,
      uniforms,
      transparent: true,
      // let the alpha from shader blend with image
      depthTest: false,
      depthWrite: false,
      blending: THREE.NormalBlending
    });
    const mesh = new THREE.Mesh(geometry, material);
    mesh.frustumCulled = false;
    scene.add(mesh);
    const baseDpr = (() => {
      if (dprAttr && dprAttr !== "auto") {
        return clamp(Number(dprAttr) || 1, 0.6, 2);
      }
      return Math.min(window.devicePixelRatio || 1, 2);
    })();
    let currentDpr = baseDpr;
    function setSize() {
      const w = el.clientWidth || 1;
      const h = el.clientHeight || 1;
      renderer.setPixelRatio(currentDpr);
      renderer.setSize(w, h, false);
      uniforms.iResolution.value.set(w * currentDpr, h * currentDpr, currentDpr);
      rect = canvas.getBoundingClientRect();
    }
    let rect = null;
    const ro = new ResizeObserver(() => requestAnimationFrame(setSize));
    ro.observe(el);
    let inView = true;
    const io = new IntersectionObserver(
      (entries) => {
        var _a;
        inView = ((_a = entries[0]) == null ? void 0 : _a.isIntersecting) ?? true;
      },
      { root: null, threshold: 0 }
    );
    io.observe(el);
    let paused = document.hidden;
    const onVis = () => {
      paused = document.hidden;
    };
    document.addEventListener("visibilitychange", onVis, { passive: true });
    const mouseTarget = new THREE.Vector2(0, 0);
    const mouseSmooth = new THREE.Vector2(0, 0);
    const updateMouse = (clientX, clientY) => {
      if (!rect) return;
      const x = clientX - rect.left;
      const y = clientY - rect.top;
      const ratio = currentDpr;
      const hb = rect.height * ratio;
      mouseTarget.set(x * ratio, hb - y * ratio);
    };
    const onMove = (ev) => updateMouse(ev.clientX, ev.clientY);
    const onLeave = () => mouseTarget.set(0, 0);
    const mouseHost = pointerEvents === "none" ? el : canvas;
    mouseHost.addEventListener("pointermove", onMove, { passive: true });
    mouseHost.addEventListener("pointerdown", onMove, { passive: true });
    mouseHost.addEventListener("pointerenter", onMove, { passive: true });
    mouseHost.addEventListener("pointerleave", onLeave, { passive: true });
    const onCtxLost = (e) => {
      e.preventDefault();
      paused = true;
    };
    const onCtxRestored = () => {
      paused = false;
      requestAnimationFrame(setSize);
    };
    canvas.addEventListener("webglcontextlost", onCtxLost, false);
    canvas.addEventListener("webglcontextrestored", onCtxRestored, false);
    let emaDt = 16.7;
    let lastCheck = performance.now();
    let fpsSamples = [];
    const dprFloor = 0.6, lowerThresh = 50, upperThresh = 58;
    function adaptDpr(now) {
      const elapsed = now - lastCheck;
      if (elapsed < 750) return;
      if (!fpsSamples.length) {
        lastCheck = now;
        return;
      }
      const avg = fpsSamples.reduce((a, b2) => a + b2, 0) / fpsSamples.length;
      let next = currentDpr;
      if (avg < lowerThresh) next = clamp(currentDpr * 0.9, dprFloor, baseDpr);
      else if (avg > upperThresh && currentDpr < baseDpr)
        next = clamp(currentDpr * 1.05, dprFloor, baseDpr);
      if (Math.abs(next - currentDpr) > 0.01) {
        currentDpr = next;
        setSize();
      }
      fpsSamples = [];
      lastCheck = now;
    }
    const clock = new THREE.Clock();
    let prevTime = 0;
    let fade = 0;
    let raf = 0;
    function animate() {
      raf = requestAnimationFrame(animate);
      if (paused || !inView) return;
      const t = clock.getElapsedTime();
      const dt = Math.max(0, t - prevTime);
      prevTime = t;
      const dtMs = dt * 1e3;
      emaDt = emaDt * 0.9 + dtMs * 0.1;
      const instFps = 1e3 / Math.max(1, emaDt);
      fpsSamples.push(instFps);
      uniforms.iTime.value = t;
      const cdt = Math.min(0.033, Math.max(1e-3, dt));
      uniforms.uFlowTime.value += cdt;
      uniforms.uFogTime.value += cdt;
      if (fade < 1) {
        fade = Math.min(1, fade + cdt / 1);
        uniforms.uFade.value = fade;
      }
      const tau = Math.max(1e-3, mouseSmoothTime);
      const alpha = 1 - Math.exp(-cdt / tau);
      mouseSmooth.lerp(mouseTarget, alpha);
      uniforms.iMouse.value.set(mouseSmooth.x, mouseSmooth.y, 0, 0);
      renderer.render(scene, camera);
      adaptDpr(performance.now());
    }
    setSize();
    animate();
    function update(opts = {}) {
      if (opts.color) {
        const { r: r2, g: g2, b: b2 } = hexToRGB(opts.color);
        uniforms.uColor.value.set(r2, g2, b2);
      }
      if (opts.horizontalSizing != null) uniforms.uHLenFactor.value = +opts.horizontalSizing;
      if (opts.verticalSizing != null) uniforms.uVLenFactor.value = +opts.verticalSizing;
      if (opts.wispDensity != null) uniforms.uWispDensity.value = +opts.wispDensity;
      if (opts.wispSpeed != null)
        uniforms.uWSpeed.value = +opts.wispSpeed * (prefersReduced ? 1e-4 : 1);
      if (opts.wispIntensity != null) uniforms.uWIntensity.value = +opts.wispIntensity;
      if (opts.flowSpeed != null)
        uniforms.uFlowSpeed.value = +opts.flowSpeed * (prefersReduced ? 1e-4 : 1);
      if (opts.flowStrength != null) uniforms.uFlowStrength.value = +opts.flowStrength;
      if (opts.fogIntensity != null)
        uniforms.uFogIntensity.value = +opts.fogIntensity * (prefersReduced ? 0.3 : 1);
      if (opts.fogScale != null) uniforms.uFogScale.value = +opts.fogScale;
      if (opts.fogFallSpeed != null)
        uniforms.uFogFallSpeed.value = +opts.fogFallSpeed * (prefersReduced ? 1e-4 : 1);
      if (opts.decay != null) uniforms.uDecay.value = +opts.decay;
      if (opts.falloffStart != null) uniforms.uFalloffStart.value = +opts.falloffStart;
      if (opts.beamX != null) uniforms.uBeamXFrac.value = +opts.beamX;
      if (opts.beamY != null) uniforms.uBeamYFrac.value = +opts.beamY;
    }
    function destroy() {
      cancelAnimationFrame(raf);
      ro.disconnect();
      io.disconnect();
      document.removeEventListener("visibilitychange", onVis);
      mouseHost.removeEventListener("pointermove", onMove);
      mouseHost.removeEventListener("pointerdown", onMove);
      mouseHost.removeEventListener("pointerenter", onMove);
      mouseHost.removeEventListener("pointerleave", onLeave);
      canvas.removeEventListener("webglcontextlost", onCtxLost);
      canvas.removeEventListener("webglcontextrestored", onCtxRestored);
      geometry.dispose();
      material.dispose();
      renderer.dispose();
      if (el.contains(canvas)) el.removeChild(canvas);
    }
    el.__laserFlow = { update, destroy, renderer, uniforms };
    return el.__laserFlow;
  }
  function autoInit() {
    document.querySelectorAll(".laser-flow").forEach((el) => {
      if (el.__laserFlow) return;
      initLaserFlow(el);
    });
  }
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", autoInit);
  } else {
    autoInit();
  }
  window.LaserFlow = { init: initLaserFlow };
})();
const parseFloatDataset = (dataset, key, defaultValue) => {
  const value = dataset[key];
  return value === void 0 ? defaultValue : Number.parseFloat(value);
};
const revealElement = (elem) => {
  const { dataset } = elem;
  const wordDelay = parseFloatDataset(dataset, "wordDelay", 0);
  const duration = parseFloatDataset(dataset, "wordDuration", 0.6);
  const offsetY = parseFloatDataset(dataset, "wordOffsetY", 30);
  const rotationX = parseFloatDataset(dataset, "wordRotationX", 95);
  const instant = dataset.wordInstant !== void 0 && dataset.wordInstant !== "false";
  const start = dataset.wordStart || "top 85%";
  const end = dataset.wordEnd || "top 40%";
  const delay = parseFloatDataset(dataset, "delay", 0);
  if (typeof SplitText === "undefined") {
    console.warn("GSAP SplitText is not available");
    return;
  }
  const splitText = new SplitText(elem, {
    type: "words"
  });
  const wordSpans = splitText.words;
  if (!(wordSpans == null ? void 0 : wordSpans.length)) {
    return;
  }
  elem.style.opacity = 1;
  elem._splitTextInstance = splitText;
  gsap.set(elem, { overflow: "hidden" });
  gsap.set(wordSpans, {
    y: offsetY,
    rotationX,
    transformOrigin: "center center",
    transformStyle: "preserve-3d",
    perspective: "1000px"
  });
  const animationProps = {
    opacity: 1,
    y: 0,
    rotationX: 0,
    duration,
    delay,
    ease: "cubic-bezier(0.22, 1, 0.36, 1)"
  };
  if (wordDelay > 0) {
    animationProps.stagger = wordDelay;
  }
  if (!instant) {
    animationProps.scrollTrigger = {
      trigger: elem,
      start,
      end,
      once: true
    };
  }
  gsap.to(wordSpans, animationProps);
};
const headingTextReveal = {
  init() {
    document.querySelectorAll("[data-opai-text-reveal]").forEach(revealElement);
  }
};
document.addEventListener("DOMContentLoaded", () => {
  headingTextReveal.init();
});
class AccordionManager {
  constructor(options = {}) {
    this.options = {
      // Default options
      allowMultiple: false,
      animationDuration: 300,
      easing: "ease-in-out",
      autoClose: true,
      keyboardNavigation: true,
      ...options
    };
    this.accordions = /* @__PURE__ */ new Map();
    this.init();
  }
  init() {
    document.addEventListener("DOMContentLoaded", () => {
      this.initializeAllAccordions();
    });
  }
  initializeAllAccordions() {
    const containers = document.querySelectorAll(".accordion");
    containers.forEach((container, index) => {
      this.createAccordion(container, index);
    });
    this.ensureSingleActiveItem();
    this.addStaggerToAccordionItems();
  }
  createAccordion(container, id) {
    const items = container.querySelectorAll(".accordion-item");
    if (!items.length) return;
    container.setAttribute("itemscope", "");
    container.setAttribute("itemtype", "https://schema.org/FAQPage");
    container.setAttribute("role", "tablist");
    container.setAttribute("aria-label", "Frequently Asked Questions");
    const accordion = {
      id,
      container,
      items: /* @__PURE__ */ new Map(),
      activeItems: /* @__PURE__ */ new Set(),
      isAnimating: false
    };
    items.forEach((item, itemIndex) => {
      this.initializeAccordionItem(accordion, item, itemIndex);
    });
    if (this.options.keyboardNavigation) {
      this.setupKeyboardNavigation(accordion);
    }
    this.accordions.set(id, accordion);
  }
  initializeAccordionItem(accordion, item, itemIndex) {
    const button = item.querySelector(".accordion-action");
    const content = item.querySelector(".accordion-content");
    if (!button || !content) return;
    this.addSEOAndAccessibilityAttributes(accordion, item, button, content, itemIndex);
    const iconWrapper = item.querySelector(".accordion-action span");
    const svgIcon = item.querySelector(".accordion-action svg");
    const plusIcon = item.querySelector(".accordion-plus-icon");
    let icon = null;
    let iconType = "none";
    if (svgIcon) {
      icon = svgIcon;
      iconType = "svg";
    } else if (plusIcon) {
      icon = plusIcon;
      iconType = "div";
    } else if (iconWrapper == null ? void 0 : iconWrapper.querySelector("svg")) {
      icon = iconWrapper.querySelector("svg");
      iconType = "svg-in-span";
    }
    const itemData = {
      item,
      button,
      content,
      icon,
      iconType,
      isOpen: false,
      isAnimating: false,
      itemIndex
    };
    this.setInitialState(itemData);
    button.addEventListener("click", (e) => {
      e.preventDefault();
      this.toggleItem(accordion, itemData);
    });
    accordion.items.set(itemIndex, itemData);
  }
  setInitialState(itemData) {
    const { item, button, content } = itemData;
    const isInitiallyOpen = item.classList.contains("default-open");
    if (isInitiallyOpen) {
      this.openItem(itemData, false);
      const accordion = this.findAccordionByItem(item);
      if (accordion) {
        accordion.activeItems.add(itemData.itemIndex);
      }
      this.applyBackgroundColor(itemData, "open");
    } else {
      this.closeItem(itemData, false);
      this.applyBackgroundColor(itemData, "closed");
    }
  }
  toggleItem(accordion, itemData) {
    if (accordion.isAnimating || itemData.isAnimating) return;
    if (itemData.isOpen) {
      this.closeItem(itemData);
      accordion.activeItems.delete(itemData.itemIndex);
    } else {
      if (!this.options.allowMultiple) {
        this.closeAllItems(accordion);
      }
      this.openItem(itemData);
      accordion.activeItems.add(itemData.itemIndex);
    }
  }
  openItem(itemData, animate = true) {
    const { item, button, content, icon, iconType } = itemData;
    if (itemData.isAnimating) return;
    itemData.isAnimating = true;
    itemData.isOpen = true;
    content.style.overflow = "hidden";
    content.style.transition = `height ${this.options.animationDuration}ms ${this.options.easing}, opacity ${this.options.animationDuration}ms ${this.options.easing}`;
    if (animate) {
      content.style.height = "auto";
      const targetHeight = content.scrollHeight;
      content.style.height = "0px";
      content.style.opacity = "0";
      content.offsetHeight;
      this.prepareContentForAnimation(content, "in");
      this.animateContentIn(content);
      requestAnimationFrame(() => {
        content.style.height = `${targetHeight}px`;
        content.style.opacity = "1";
      });
      const handleTransitionEnd = (e) => {
        if (e.propertyName === "height") {
          content.style.height = "auto";
          content.removeEventListener("transitionend", handleTransitionEnd);
          itemData.isAnimating = false;
        }
      };
      content.addEventListener("transitionend", handleTransitionEnd);
    } else {
      content.style.height = "auto";
      content.style.opacity = "1";
      itemData.isAnimating = false;
      this.prepareContentForAnimation(content, "in");
      this.animateContentIn(content);
    }
    this.updateIcon(itemData, "open");
    button.setAttribute("aria-expanded", "true");
    content.setAttribute("aria-hidden", "false");
    item.setAttribute("aria-expanded", "true");
    item.setAttribute("aria-selected", "true");
    item.classList.add("active");
    this.applyBackgroundColor(itemData, "open");
  }
  closeItem(itemData, animate = true) {
    const { item, button, content, icon, iconType } = itemData;
    if (itemData.isAnimating) return;
    itemData.isAnimating = true;
    itemData.isOpen = false;
    content.style.overflow = "hidden";
    content.style.transition = `height ${this.options.animationDuration}ms ${this.options.easing}, opacity ${this.options.animationDuration}ms ${this.options.easing}`;
    if (animate) {
      this.prepareContentForAnimation(content, "out");
      this.animateContentOut(content);
      const currentHeight = content.scrollHeight;
      content.style.height = `${currentHeight}px`;
      content.offsetHeight;
      setTimeout(() => {
        requestAnimationFrame(() => {
          content.style.height = "0px";
          content.style.opacity = "0";
        });
      }, 100);
      const handleTransitionEnd = (e) => {
        if (e.propertyName === "height") {
          content.removeEventListener("transitionend", handleTransitionEnd);
          itemData.isAnimating = false;
          this.cleanupSplitText(content);
        }
      };
      content.addEventListener("transitionend", handleTransitionEnd);
    } else {
      content.style.height = "0px";
      content.style.opacity = "0";
      itemData.isAnimating = false;
      this.cleanupSplitText(content);
    }
    this.updateIcon(itemData, "closed");
    button.setAttribute("aria-expanded", "false");
    content.setAttribute("aria-hidden", "true");
    item.setAttribute("aria-expanded", "false");
    item.setAttribute("aria-selected", "false");
    item.classList.remove("active");
    this.applyBackgroundColor(itemData, "closed");
  }
  closeAllItems(accordion) {
    accordion.items.forEach((itemData) => {
      if (itemData.isOpen) {
        this.cleanupSplitText(itemData.content);
        this.closeItem(itemData);
        accordion.activeItems.delete(itemData.itemIndex);
      }
    });
  }
  findAccordionByItem(item) {
    for (const accordion of this.accordions.values()) {
      for (const itemData of accordion.items.values()) {
        if (itemData.item === item) {
          return accordion;
        }
      }
    }
    return null;
  }
  ensureSingleActiveItem() {
    this.accordions.forEach((accordion) => {
      const activeItems = Array.from(accordion.items.values()).filter(
        (itemData) => itemData.item.classList.contains("default-open")
      );
      if (activeItems.length > 1) {
        activeItems.slice(1).forEach((itemData) => {
          itemData.item.classList.remove("default-open");
          this.closeItem(itemData, false);
          accordion.activeItems.delete(itemData.itemIndex);
        });
      }
      if (activeItems.length === 0) {
        accordion.items.forEach((itemData) => {
          if (itemData.isOpen) {
            this.closeItem(itemData, false);
            accordion.activeItems.delete(itemData.itemIndex);
          }
        });
      }
    });
  }
  updateIcon(itemData, state) {
    const { icon, iconType } = itemData;
    if (!icon) return;
    if (iconType === "svg" || iconType === "svg-in-span") {
      if (typeof gsap !== "undefined") {
        gsap.killTweensOf(icon);
        gsap.to(icon, {
          rotation: state === "open" ? 180 : 0,
          duration: this.options.animationDuration / 1e3,
          ease: state === "open" ? "power3.out" : "power3.in"
        });
      } else {
        icon.style.transform = state === "open" ? "rotate(180deg)" : "rotate(0deg)";
        icon.style.transition = `transform ${this.options.animationDuration}ms ${this.options.easing}`;
      }
    } else if (iconType === "div") {
      if (state === "open") {
        itemData.item.classList.add("active");
      } else {
        itemData.item.classList.remove("active");
      }
    }
  }
  applyBackgroundColor(itemData, state) {
    const { item } = itemData;
    const bgColor = item.getAttribute("data-active-bg-color");
    if (!bgColor) return;
    item.style.transition = `background-color ${this.options.animationDuration}ms ${this.options.easing}`;
    if (state === "open") {
      if (typeof gsap !== "undefined") {
        gsap.killTweensOf(item);
        gsap.to(item, {
          backgroundColor: bgColor,
          duration: this.options.animationDuration / 1e3,
          ease: "power2.out"
        });
      } else {
        item.style.backgroundColor = bgColor;
      }
    } else {
      if (typeof gsap !== "undefined") {
        gsap.killTweensOf(item);
        gsap.to(item, {
          backgroundColor: "transparent",
          duration: this.options.animationDuration / 1e3,
          ease: "power2.in"
        });
      } else {
        item.style.backgroundColor = "transparent";
      }
    }
  }
  animateContentIn(content) {
    if (typeof gsap === "undefined" || typeof SplitText === "undefined") {
      return;
    }
    const textElements = content.querySelectorAll("p, span, div, h1, h2, h3, h4, h5, h6");
    if (!textElements.length) return;
    const accordionDuration = this.options.animationDuration / 1e3;
    const textDuration = Math.max(0.6, accordionDuration * 0.8);
    textElements.forEach((element, index) => {
      if (element._splitTextInstance) {
        element._splitTextInstance.revert();
        element._splitTextInstance = null;
      }
      gsap.killTweensOf(element);
      gsap.set(element, { clearProps: "all" });
      if (element.tagName === "P" && element.textContent.trim()) {
        const splitText = new SplitText(element, { type: "lines" });
        const lines = splitText.lines;
        element._splitTextInstance = splitText;
        gsap.set(lines, {
          opacity: 0,
          y: 30,
          rotationX: -90
        });
        gsap.to(lines, {
          opacity: 1,
          y: 0,
          rotationX: 0,
          duration: textDuration,
          ease: "power2.out",
          stagger: 0.08,
          delay: index * 0.05
        });
      } else {
        gsap.set(element, { opacity: 0, y: 20 });
        gsap.to(element, {
          opacity: 1,
          y: 0,
          duration: textDuration * 0.8,
          ease: "power2.out",
          delay: index * 0.03
        });
      }
    });
  }
  animateContentOut(content) {
    if (typeof gsap === "undefined" || typeof SplitText === "undefined") {
      return;
    }
    const textElements = content.querySelectorAll("p, span, div, h1, h2, h3, h4, h5, h6");
    if (!textElements.length) return;
    const accordionDuration = this.options.animationDuration / 1e3;
    const textDuration = Math.max(0.3, accordionDuration * 0.6);
    textElements.forEach((element, index) => {
      gsap.killTweensOf(element);
      if (element.tagName === "P" && element.textContent.trim()) {
        const splitText = new SplitText(element, { type: "lines" });
        const lines = splitText.lines;
        element._splitTextInstance = splitText;
        gsap.to(lines, {
          opacity: 0,
          y: -20,
          rotationX: 90,
          duration: textDuration,
          ease: "power2.in",
          stagger: 0.03,
          delay: index * 0.02
        });
      } else {
        gsap.to(element, {
          opacity: 0,
          y: -15,
          duration: textDuration * 0.8,
          ease: "power2.in",
          delay: index * 0.02
        });
      }
    });
  }
  cleanupSplitText(content) {
    if (typeof gsap === "undefined" || typeof SplitText === "undefined") {
      return;
    }
    const textElements = content.querySelectorAll("p, span, div, h1, h2, h3, h4, h5, h6");
    textElements.forEach((element) => {
      gsap.killTweensOf(element);
      if (element._splitTextInstance) {
        element._splitTextInstance.revert();
        element._splitTextInstance = null;
      }
      gsap.set(element, { clearProps: "all" });
    });
  }
  // Prevent animation conflicts by ensuring clean state
  prepareContentForAnimation(content, direction = "in") {
    if (typeof gsap === "undefined" || typeof SplitText === "undefined") {
      return;
    }
    const textElements = content.querySelectorAll("p, span, div, h1, h2, h3, h4, h5, h6");
    textElements.forEach((element) => {
      gsap.killTweensOf(element);
      if (element._splitTextInstance) {
        element._splitTextInstance.revert();
        element._splitTextInstance = null;
      }
      gsap.set(element, {
        clearProps: "all",
        opacity: direction === "in" ? 0 : 1
      });
    });
  }
  addSEOAndAccessibilityAttributes(accordion, item, button, content, itemIndex) {
    item.setAttribute("itemscope", "");
    item.setAttribute("itemprop", "mainEntity");
    item.setAttribute("itemtype", "https://schema.org/Question");
    item.setAttribute("role", "tab");
    item.setAttribute("tabindex", "0");
    const isInitiallyOpen = item.classList.contains("default-open");
    item.setAttribute("aria-expanded", isInitiallyOpen ? "true" : "false");
    item.setAttribute("aria-selected", isInitiallyOpen ? "true" : "false");
    const questionId = `faq-question-${accordion.id}-${itemIndex}`;
    const answerId = `faq-answer-${accordion.id}-${itemIndex}`;
    button.setAttribute("id", questionId);
    button.setAttribute("aria-controls", answerId);
    button.setAttribute("aria-expanded", isInitiallyOpen ? "true" : "false");
    content.setAttribute("id", answerId);
    content.setAttribute("role", "tabpanel");
    content.setAttribute("aria-labelledby", questionId);
    content.setAttribute("aria-hidden", isInitiallyOpen ? "false" : "true");
    content.setAttribute("itemscope", "");
    content.setAttribute("itemprop", "acceptedAnswer");
    content.setAttribute("itemtype", "https://schema.org/Answer");
    button.setAttribute("itemprop", "name");
    const answerText = content.querySelector("p");
    if (answerText) {
      answerText.setAttribute("itemprop", "text");
    }
    const plusIcon = item.querySelector(".accordion-plus-icon");
    if (plusIcon) {
      plusIcon.setAttribute("aria-hidden", "true");
    }
  }
  setupKeyboardNavigation(accordion) {
    accordion.container.addEventListener("keydown", (e) => {
      if (!e.target.closest(".accordion-action")) return;
      const items = Array.from(accordion.items.values());
      const currentIndex = items.findIndex((item) => item.button === e.target);
      if (currentIndex === -1) return;
      switch (e.key) {
        case "ArrowDown":
          e.preventDefault();
          const nextIndex = (currentIndex + 1) % items.length;
          items[nextIndex].button.focus();
          break;
        case "ArrowUp":
          e.preventDefault();
          const prevIndex = currentIndex === 0 ? items.length - 1 : currentIndex - 1;
          items[prevIndex].button.focus();
          break;
        case "Home":
          e.preventDefault();
          items[0].button.focus();
          break;
        case "End":
          e.preventDefault();
          items[items.length - 1].button.focus();
          break;
        case "Enter":
        case " ":
          e.preventDefault();
          this.toggleItem(accordion, items[currentIndex]);
          break;
      }
    });
  }
  addStaggerToAccordionItems() {
    const accordionContainers = document.querySelectorAll(".accordion");
    accordionContainers.forEach((container) => {
      const items = container.querySelectorAll(".accordion-item");
      if (!items.length) return;
      items.forEach((item, index) => {
        const staggerDelay = index * 0.1;
        item.setAttribute("data-opai-animate", "");
        item.setAttribute("data-delay", staggerDelay.toString());
        item.setAttribute("data-accordion-stagger", "true");
      });
    });
  }
  // Public API methods
  openItemById(accordionId, itemIndex) {
    const accordion = this.accordions.get(accordionId);
    if (!accordion) return;
    const itemData = accordion.items.get(itemIndex);
    if (!itemData) return;
    this.toggleItem(accordion, itemData);
  }
  closeItemById(accordionId, itemIndex) {
    const accordion = this.accordions.get(accordionId);
    if (!accordion) return;
    const itemData = accordion.items.get(itemIndex);
    if (!itemData || !itemData.isOpen) return;
    this.toggleItem(accordion, itemData);
  }
  openAllItems(accordionId) {
    const accordion = this.accordions.get(accordionId);
    if (!accordion) return;
    accordion.items.forEach((itemData) => {
      if (!itemData.isOpen) {
        this.openItem(itemData);
        accordion.activeItems.add(itemData.itemIndex);
      }
    });
  }
  closeAllItemsById(accordionId) {
    const accordion = this.accordions.get(accordionId);
    if (!accordion) return;
    this.closeAllItems(accordion);
  }
  destroy(accordionId) {
    const accordion = this.accordions.get(accordionId);
    if (!accordion) return;
    accordion.items.forEach((itemData) => {
      const { button, content } = itemData;
      button.removeEventListener("click", this.toggleItem);
      this.cleanupSplitText(content);
    });
    this.accordions.delete(accordionId);
  }
  destroyAll() {
    this.accordions.forEach((accordion, id) => {
      this.destroy(id);
    });
  }
}
new AccordionManager({
  allowMultiple: false,
  animationDuration: 300,
  easing: "ease-in-out",
  autoClose: true,
  keyboardNavigation: true
});
const avatar = {
  init() {
    if (typeof gsap === "undefined" || typeof ScrollTrigger === "undefined") return;
    gsap.registerPlugin(ScrollTrigger);
    const avatars = document.querySelectorAll("[data-opai-avatar]");
    avatars.forEach((el) => {
      const delay = el.dataset.avatarDelay ? Number.parseFloat(el.dataset.avatarDelay) : 0;
      const direction = el.dataset.avatarDirection || "left";
      const offset = el.dataset.avatarOffset ? Number.parseFloat(el.dataset.avatarOffset) : 0;
      const from = { opacity: 0, scale: 0, filter: "blur(5px)" };
      switch (direction) {
        case "left":
          from.x = -offset;
          break;
        case "right":
          from.x = offset;
          break;
        case "down":
          from.y = offset;
          break;
        case "up":
        default:
          from.y = -offset;
          break;
      }
      gsap.fromTo(
        el,
        from,
        {
          opacity: 1,
          scale: 1,
          x: 0,
          y: 0,
          filter: "blur(0px)",
          duration: 1.5,
          delay,
          ease: "elastic.out(1, 0.7)",
          scrollTrigger: {
            trigger: el,
            start: "top 90%",
            end: "bottom 20%"
          }
        }
      );
    });
  }
};
document.addEventListener("DOMContentLoaded", () => {
  avatar.init();
});
const borderExpand = {
  init() {
    const lengthElements = document.querySelectorAll("[data-opai-border-expand]");
    lengthElements.forEach((element) => {
      const ElementFinalWidth = element.offsetWidth;
      const delay = element.getAttribute("data-delay") ? parseFloat(element.getAttribute("data-delay")) : 0;
      const top = element.getAttribute("data-top") ? element.getAttribute("data-top") : "top 100%";
      const markerId = element.getAttribute("data-marker-id") ? element.getAttribute("data-marker-id") : false;
      const duration = element.getAttribute("data-duration") ? parseFloat(element.getAttribute("data-duration")) : 0.6;
      gsap.set(element, {
        width: 0
      });
      gsap.to(element, {
        scrollTrigger: {
          trigger: element,
          start: top,
          end: "top 100%",
          toggleActions: "play none none none",
          markers: markerId ? true : false,
          id: markerId && markerId
        },
        width: ElementFinalWidth,
        duration,
        ease: "power3.out",
        delay
      });
    });
  }
};
document.addEventListener("DOMContentLoaded", () => {
  borderExpand.init();
});
const footerTextShuffle = {
  init() {
    const elements = document.querySelectorAll(".footer-title, .footer-title-2");
    if (!elements.length) return;
    elements.forEach((element) => {
      var _a;
      element.style.whiteSpace = "nowrap";
      const spanElement = element.querySelector("span");
      const textNode = spanElement || element;
      const originalText = ((_a = textNode.textContent) == null ? void 0 : _a.trim()) || "OPTIM AI";
      const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
      const duration = 2e3;
      let startTime = null;
      const animate = (currentTime) => {
        if (!startTime) startTime = currentTime;
        const progress = Math.min((currentTime - startTime) / duration, 1);
        const revealed = Math.floor(progress * originalText.length);
        const animatedText = originalText.split("").map((char, i) => {
          if (char === " ") return " ";
          return i < revealed ? originalText[i] : chars[Math.floor(Math.random() * 26)];
        }).join("");
        if (spanElement) {
          spanElement.textContent = animatedText;
        } else {
          element.textContent = animatedText;
        }
        if (progress < 1) {
          requestAnimationFrame(animate);
        } else {
          if (spanElement) {
            spanElement.textContent = originalText;
          } else {
            element.textContent = originalText;
          }
        }
      };
      const shuffle = () => {
        startTime = null;
        requestAnimationFrame(animate);
      };
      if (typeof ScrollTrigger !== "undefined") {
        ScrollTrigger.create({
          trigger: element,
          start: "top 100%",
          onEnter: shuffle,
          once: true
        });
      } else {
        shuffle();
      }
    });
  }
};
document.addEventListener("DOMContentLoaded", () => footerTextShuffle.init());
document.addEventListener("DOMContentLoaded", function() {
  if (typeof InfiniteMarquee === "undefined") {
    console.error("InfiniteMarquee is not loaded.");
    return;
  }
  const animation2 = {
    infiniteLeft() {
      if (document.querySelector(".logos-marquee-container")) {
        new InfiniteMarquee({
          element: ".logos-marquee-container",
          speed: 55e3,
          smoothEdges: true,
          direction: "left",
          gap: "32px",
          duplicateCount: 1,
          mobileSettings: {
            direction: "top",
            speed: 5e4
          },
          on: {
            beforeInit: () => console.log("Not Yet Initialized"),
            afterInit: () => console.log("Initialized")
          }
        });
      }
    },
    infiniteRight() {
      if (document.querySelector(".logos-right-marquee-container")) {
        new InfiniteMarquee({
          element: ".logos-right-marquee-container",
          speed: 55e3,
          smoothEdges: true,
          direction: "right",
          gap: "32px",
          duplicateCount: 1,
          mobileSettings: {
            direction: "right",
            speed: 5e4
          },
          on: {
            beforeInit: () => console.log("Not Yet Initialized"),
            afterInit: () => console.log("Initialized")
          }
        });
      }
    },
    initHover() {
      if (document.querySelector(".cards-marquee-container")) {
        new InfiniteMarquee({
          element: ".cards-marquee-container",
          speed: 14e4,
          smoothEdges: true,
          direction: "left",
          gap: "32px",
          pauseOnHover: true,
          on: {
            beforeInit: () => console.log("Not Yet Initialized"),
            afterInit: () => console.log("Initialized")
          }
        });
      }
    },
    initHoverRight() {
      if (document.querySelector(".cards-right-marquee-container")) {
        new InfiniteMarquee({
          element: ".cards-right-marquee-container",
          speed: 14e4,
          smoothEdges: true,
          direction: "right",
          gap: "32px",
          pauseOnHover: true,
          on: {
            beforeInit: () => console.log("Not Yet Initialized"),
            afterInit: () => console.log("Initialized")
          }
        });
      }
    }
  };
  animation2.infiniteLeft();
  animation2.infiniteRight();
  animation2.initHover();
  animation2.initHoverRight();
});
class MobileMenuAccordion {
  constructor(options = {}) {
    this.defaultOpenMenu = options.defaultOpenMenu || "company-mega-menu";
    this.toggleButtons = null;
    this.submenus = null;
    this.arrows = null;
    this.init();
  }
  init() {
    if (document.readyState === "loading") {
      document.addEventListener("DOMContentLoaded", () => this.bindEvents());
    } else {
      this.bindEvents();
    }
  }
  bindEvents() {
    this.toggleButtons = document.querySelectorAll(".mobile-menu-toggle[data-menu]");
    if (this.toggleButtons.length === 0) {
      return;
    }
    this.submenus = document.querySelectorAll(".mobile-submenu[data-submenu]");
    this.arrows = document.querySelectorAll(".mobile-menu-toggle .menu-arrow");
    this.setDefaultState();
    this.toggleButtons.forEach((button) => {
      button.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        const menuId = button.dataset.menu;
        if (!menuId) return;
        this.toggleMenu(menuId);
      });
    });
  }
  setDefaultState() {
    this.submenus.forEach((submenu) => {
      submenu.classList.add("hidden");
      submenu.classList.remove("block");
    });
    this.arrows.forEach((arrow) => {
      arrow.classList.remove("rotate-90");
    });
    if (this.defaultOpenMenu) {
      const defaultSubmenu = document.querySelector(
        `.mobile-submenu[data-submenu="${this.defaultOpenMenu}"]`
      );
      const defaultButton = document.querySelector(
        `.mobile-menu-toggle[data-menu="${this.defaultOpenMenu}"]`
      );
      const defaultArrow = defaultButton == null ? void 0 : defaultButton.querySelector(".menu-arrow");
      if (defaultSubmenu) {
        defaultSubmenu.classList.remove("hidden");
        defaultSubmenu.classList.add("block");
      }
      if (defaultArrow) {
        defaultArrow.classList.add("rotate-90");
      }
    }
  }
  toggleMenu(menuId) {
    const submenu = document.querySelector(`.mobile-submenu[data-submenu="${menuId}"]`);
    const button = document.querySelector(`.mobile-menu-toggle[data-menu="${menuId}"]`);
    const arrow = button == null ? void 0 : button.querySelector(".menu-arrow");
    if (!submenu || !button) {
      return;
    }
    const isCurrentlyOpen = submenu.classList.contains("block") && !submenu.classList.contains("hidden");
    this.closeAllMenus();
    if (isCurrentlyOpen) {
      submenu.classList.add("hidden");
      submenu.classList.remove("block");
      if (arrow) {
        arrow.classList.remove("rotate-90");
      }
    } else {
      submenu.classList.remove("hidden");
      submenu.classList.add("block");
      if (arrow) {
        arrow.classList.add("rotate-90");
      }
    }
  }
  closeAllMenus() {
    this.submenus.forEach((submenu) => {
      submenu.classList.add("hidden");
      submenu.classList.remove("block");
    });
    this.arrows.forEach((arrow) => {
      arrow.classList.remove("rotate-90");
    });
  }
}
function initMobileSidebar() {
  const openBtn = document.querySelector("header .nav-hamburger");
  const sidebar = document.getElementById("mobile-sidebar");
  const closeBtn = sidebar == null ? void 0 : sidebar.querySelector(".nav-hamburger-close");
  if (!openBtn || !sidebar) return;
  let isOpen = false;
  const open = () => {
    isOpen = true;
    sidebar.classList.remove("translate-x-full");
    sidebar.classList.add("translate-x-0");
    openBtn.setAttribute("aria-expanded", "true");
    document.documentElement.classList.add("overflow-hidden");
  };
  const close = () => {
    isOpen = false;
    sidebar.classList.add("translate-x-full");
    sidebar.classList.remove("translate-x-0");
    openBtn.setAttribute("aria-expanded", "false");
    document.documentElement.classList.remove("overflow-hidden");
  };
  const toggle = () => {
    if (isOpen) close();
    else open();
  };
  openBtn.addEventListener("click", (e) => {
    e.preventDefault();
    e.stopPropagation();
    toggle();
  });
  closeBtn == null ? void 0 : closeBtn.addEventListener("click", (e) => {
    e.preventDefault();
    e.stopPropagation();
    close();
  });
  document.addEventListener("click", (e) => {
    if (!isOpen) return;
    const t = e.target;
    if (sidebar.contains(t) || openBtn.contains(t)) return;
    close();
  });
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape" && isOpen) close();
  });
}
document.addEventListener("DOMContentLoaded", () => {
  initMobileSidebar();
  const mobileMenuExists = document.querySelector(".mobile-menu-toggle[data-menu]");
  if (mobileMenuExists) {
    globalThis.mobileMenuAccordion = new MobileMenuAccordion({
      defaultOpenMenu: "company-mega-menu"
    });
  }
});
const initPricingBillingToggle = () => {
  document.querySelectorAll(".price-scope").forEach((scope) => {
    const toggle = scope.querySelector("[data-pricing-billing-toggle]");
    const priceBlocks = scope.querySelectorAll("[data-pricing-price]");
    if (!toggle || !priceBlocks.length) return;
    const setPrice = (block, isYearly) => {
      const flow = block.querySelector("[data-pricing-number]");
      if (!(flow == null ? void 0 : flow.update)) return;
      const value = Number(
        isYearly ? block.dataset.pricingYearly : block.dataset.pricingMonthly
      );
      const period = block.querySelector("[data-pricing-period]");
      if (period) period.textContent = isYearly ? "yr" : "mo";
      flow.transformTiming = { duration: 1200, easing: "ease-out" };
      flow.spinTiming = { duration: 1200, easing: "ease-out" };
      flow.update(value);
    };
    priceBlocks.forEach((block) => {
      const flow = block.querySelector("[data-pricing-number]");
      if (!(flow == null ? void 0 : flow.update)) return;
      flow.format = {
        useGrouping: true,
        maximumFractionDigits: 0,
        minimumFractionDigits: 0
      };
      flow.update(Number(block.dataset.pricingMonthly) || 0);
    });
    toggle.addEventListener("change", () => {
      const isYearly = toggle.checked;
      priceBlocks.forEach((block) => setPrice(block, isYearly));
    });
  });
};
const bootPricingBillingToggle = () => {
  if (customElements.get("number-flow")) {
    initPricingBillingToggle();
    return;
  }
  customElements.whenDefined("number-flow").then(initPricingBillingToggle);
};
document.addEventListener("DOMContentLoaded", bootPricingBillingToggle);
const progressiveBlurEffect = {
  init() {
    const blurElements = document.querySelectorAll("[data-progressive-blur-effect]");
    blurElements.forEach((element) => {
      const intensity = element.dataset.intensity ? Number.parseFloat(element.dataset.intensity) : 50;
      const position = element.dataset.position ? element.dataset.position : "top";
      const className = element.dataset.class ? element.dataset.class : "";
      const intensityFactor = intensity / 50;
      const blurLayers = [
        { blur: `${1 * intensityFactor}px`, maskStart: 0, maskEnd: 25, zIndex: 1 },
        { blur: `${3 * intensityFactor}px`, maskStart: 25, maskEnd: 75, zIndex: 2 },
        { blur: `${6 * intensityFactor}px`, maskStart: 75, maskEnd: 100, zIndex: 3 }
      ];
      const positionStyles = {
        bottom: { bottom: "0", left: "0", right: "0", top: "auto" },
        top: { top: "0", left: "0", right: "0", bottom: "auto" },
        left: { left: "0", top: "0", bottom: "0", right: "auto" },
        right: { right: "0", top: "0", bottom: "0", left: "auto" }
      };
      const gradientDirection = {
        bottom: "to bottom",
        top: "to top",
        left: "to left",
        right: "to right"
      };
      Object.assign(
        element.style,
        {
          position: "absolute",
          zIndex: "10",
          pointerEvents: "auto"
        },
        positionStyles[position]
      );
      if (className) {
        className.split(" ").forEach((cls) => {
          if (cls.trim()) {
            element.classList.add(cls.trim());
          }
        });
      }
      blurLayers.forEach((layer, index) => {
        const layerElement = document.createElement("div");
        const maskImage = `linear-gradient(${gradientDirection[position]}, transparent ${layer.maskStart}%, black ${layer.maskEnd}%)`;
        Object.assign(layerElement.style, {
          position: "absolute",
          top: "0",
          left: "0",
          right: "0",
          bottom: "0",
          pointerEvents: "none",
          zIndex: String(layer.zIndex),
          backdropFilter: `blur(${layer.blur})`,
          WebkitBackdropFilter: `blur(${layer.blur})`,
          maskImage,
          WebkitMaskImage: maskImage
        });
        element.appendChild(layerElement);
      });
    });
  }
};
document.addEventListener("DOMContentLoaded", () => {
  progressiveBlurEffect.init();
});
const animation = {
  init() {
    const elements = document.querySelectorAll("[data-opai-animate]");
    const Springer = window.Springer.default;
    elements.forEach((elem) => {
      const duration = elem.getAttribute("data-duration") ? parseFloat(elem.getAttribute("data-duration")) : 0.6;
      const blur = elem.getAttribute("data-blur") ? parseFloat(elem.getAttribute("data-blur")) : 0;
      const delay = elem.getAttribute("data-delay") ? parseFloat(elem.getAttribute("data-delay")) : 0;
      const offset = elem.getAttribute("data-offset") ? parseFloat(elem.getAttribute("data-offset")) : 60;
      const instant = elem.hasAttribute("data-instant") && elem.getAttribute("data-instant") !== "false";
      const start = elem.getAttribute("data-start") || "top 90%";
      const end = elem.getAttribute("data-end") || "top 50%";
      const direction = elem.getAttribute("data-direction") || "down";
      const useSpring = elem.hasAttribute("data-spring");
      const spring = useSpring ? Springer(0.2, 0.8) : null;
      const rotation = elem.getAttribute("data-rotation") ? parseFloat(elem.getAttribute("data-rotation")) : 0;
      const scale = elem.getAttribute("data-scale") ? parseFloat(elem.getAttribute("data-scale")) : 1;
      const animationType = elem.getAttribute("data-animation-type") || "from";
      elem.style.opacity = "1";
      elem.style.filter = `blur(${blur}px)`;
      let animationProps;
      if (animationType === "to") {
        animationProps = {
          opacity: 1,
          filter: "blur(0)",
          duration,
          delay,
          ease: useSpring ? spring : "power2.out",
          scale
        };
        if (rotation !== 0) {
          animationProps.rotation = rotation;
        }
      } else {
        animationProps = {
          opacity: 0,
          filter: "blur(16px)",
          duration,
          delay,
          ease: useSpring ? spring : "power2.out"
        };
        if (rotation !== 0) {
          animationProps.rotation = rotation;
        }
      }
      if (!instant) {
        animationProps.scrollTrigger = {
          trigger: elem,
          start,
          end,
          scrub: false
        };
      }
      switch (direction) {
        case "left":
          animationProps.x = -offset;
          break;
        case "right":
          animationProps.x = offset;
          break;
        case "down":
          animationProps.y = offset;
          break;
        case "up":
        default:
          animationProps.y = -offset;
          break;
      }
      if (animationType === "to") {
        gsap.to(elem, animationProps);
      } else {
        gsap.from(elem, animationProps);
      }
    });
  }
};
document.addEventListener("DOMContentLoaded", () => {
  animation.init();
});
let lenis;
const smoothScrolling = () => {
  const isMobile = /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) || window.innerWidth <= 768 || "ontouchstart" in window;
  if (!isMobile) {
    lenis = new Lenis({
      lerp: 0.08,
      smoothWheel: true
    });
    lenis.on("scroll", () => ScrollTrigger.update());
    gsap.ticker.add((time) => {
      lenis.raf(time * 1e3);
    });
    gsap.ticker.lagSmoothing(0);
  }
};
const resetTocItems = (sidebarList) => {
  const allListItems = sidebarList.querySelectorAll("li");
  allListItems.forEach((item) => {
    const icon = item.querySelector("span:last-child");
    const text = item.querySelector("span:first-child, a span");
    if (icon) icon.classList.add("invisible");
    if (text) {
      text.classList.remove("font-medium", "text-secondary", "dark:text-accent");
      text.classList.add("font-normal", "text-secondary/60", "dark:text-accent/60");
    }
  });
};
const activateTocItem = (item) => {
  const icon = item.querySelector("span:last-child");
  const text = item.querySelector("span:first-child, a span");
  if (icon) icon.classList.remove("invisible");
  if (text) {
    text.classList.remove("font-normal", "text-secondary/60", "dark:text-accent/60");
    text.classList.add("font-medium", "text-secondary", "dark:text-accent");
  }
};
const handleTocItemClick = (clickedItem, sidebarList) => {
  resetTocItems(sidebarList);
  activateTocItem(clickedItem);
};
const lenisSmoothScrollLinks = () => {
  const lenisTargetElements = document.querySelectorAll(".lenis-scroll-to");
  const sidebarList = document.querySelector(".table-of-contents .table-of-list");
  lenisTargetElements.forEach((ele) => {
    ele.addEventListener("click", function(e) {
      e.preventDefault();
      const target = ele.getAttribute("href");
      if (sidebarList) {
        const clickedItem = ele.closest("li");
        if (clickedItem) {
          handleTocItemClick(clickedItem, sidebarList);
        }
      }
      if (target) {
        if (lenis) {
          lenis.scrollTo(target, {
            offset: -100,
            duration: 1.7,
            easing: (t) => 1 - Math.pow(1 - t, 3)
          });
        } else {
          const targetElement = document.querySelector(target);
          if (targetElement) {
            targetElement.scrollIntoView({
              behavior: "smooth",
              block: "start"
            });
            setTimeout(() => {
              window.scrollBy(0, -100);
            }, 100);
          }
        }
      }
    });
  });
};
const handleTocListClicks = () => {
  const sidebarList = document.querySelector(".table-of-contents .table-of-list");
  if (!sidebarList) return;
  const listItems = sidebarList.querySelectorAll("li");
  listItems.forEach((item) => {
    if (item.querySelector(".lenis-scroll-to")) {
      return;
    }
    item.addEventListener("click", function() {
      handleTocItemClick(item, sidebarList);
    });
  });
};
document.addEventListener("DOMContentLoaded", () => {
  smoothScrolling();
  lenisSmoothScrollLinks();
  handleTocListClicks();
});
const stairsCardsAnimation = {
  init() {
    const containers = document.querySelectorAll("[data-stairs-wrapper]");
    if (containers.length === 0) return;
    containers.forEach((container) => {
      const cardSelector = container.getAttribute("data-stairs-wrapper");
      const cards = container.querySelectorAll(cardSelector);
      const baseOffset = container.getAttribute("data-base-offset") ? parseFloat(container.getAttribute("data-base-offset")) : 150;
      const stepOffset = container.getAttribute("data-step-offset") ? parseFloat(container.getAttribute("data-step-offset")) : 22;
      const duration = container.getAttribute("data-duration") ? parseFloat(container.getAttribute("data-duration")) : 1.2;
      const stagger = container.getAttribute("data-stagger") ? parseFloat(container.getAttribute("data-stagger")) : 0.2;
      const start = container.getAttribute("data-start") || "top 80%";
      const end = container.getAttribute("data-end") || "top 20%";
      const useScrub = container.hasAttribute("data-scrub") && container.getAttribute("data-scrub") !== "false";
      const once = container.hasAttribute("data-once") && container.getAttribute("data-once") !== "false";
      cards.forEach((card, index) => {
        const offsetY = baseOffset + index * stepOffset;
        gsap.set(card, {
          y: offsetY
        });
      });
      const animationProps = {
        y: 0,
        duration,
        ease: "power3.out",
        stagger,
        scrollTrigger: {
          trigger: container,
          start,
          end,
          once
        }
      };
      if (useScrub) {
        animationProps.scrollTrigger.scrub = true;
      }
      gsap.to(cards, animationProps);
    });
  }
};
document.addEventListener("DOMContentLoaded", () => {
  stairsCardsAnimation.init();
});
const wordSlideReveal = {
  init() {
    const elements = document.querySelectorAll("[data-opai-word-slide-reveal]");
    elements.forEach((elem) => {
      const wordDelay = parseFloat(elem.getAttribute("data-word-delay") || "0.15");
      const duration = parseFloat(elem.getAttribute("data-word-duration") || "1");
      const offsetX = parseFloat(elem.getAttribute("data-word-offset-x") || "20");
      const blur = parseFloat(elem.getAttribute("data-word-blur") || "10");
      const instant = elem.hasAttribute("data-word-instant") && elem.getAttribute("data-word-instant") !== "false";
      const start = elem.getAttribute("data-word-start") || "top 90%";
      const delay = parseFloat(elem.getAttribute("data-delay") || "0.1");
      if (typeof SplitText === "undefined") {
        return;
      }
      const splitText = new SplitText(elem, {
        type: "words",
        tag: "span"
      });
      const wordSpans = splitText.words;
      if (!wordSpans || wordSpans.length === 0) return;
      elem.style.opacity = 1;
      elem._splitTextInstance = splitText;
      wordSpans.forEach((word) => {
        var _a;
        const wrapper = document.createElement("div");
        wrapper.style.cssText = "display: inline-block;";
        (_a = word.parentNode) == null ? void 0 : _a.insertBefore(wrapper, word);
        wrapper.appendChild(word);
      });
      gsap.fromTo(
        wordSpans,
        {
          x: offsetX,
          opacity: 0,
          display: "block",
          filter: `blur(${blur}px)`
        },
        {
          x: 0,
          opacity: 1,
          filter: "blur(0px)",
          duration,
          delay,
          ease: "power2.inOut",
          stagger: wordDelay,
          scrollTrigger: instant ? null : {
            trigger: elem,
            start,
            once: true
          }
        }
      );
    });
  }
};
document.addEventListener("DOMContentLoaded", () => {
  wordSlideReveal.init();
});
