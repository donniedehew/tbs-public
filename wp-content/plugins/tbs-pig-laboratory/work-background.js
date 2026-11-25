(() => {
  const canvas = document.getElementById('tbs-work-background');
  if (!canvas) return;
  const gl = canvas.getContext('webgl', { alpha: false, antialias: false, powerPreference: 'low-power' });
  if (!gl) return;

  const vertex = `attribute vec2 position; varying vec2 uv; void main(){ uv=position*.5+.5; gl_Position=vec4(position,0.,1.); }`;
  const fragment = `precision mediump float;
    varying vec2 uv; uniform vec2 resolution; uniform vec2 pointer; uniform float time;
    float grain(vec2 p){return fract(52.9829189*fract(dot(p,vec2(.06711056,.00583715))));}
    void main(){
      float t=time*.23;
      vec2 q=uv+vec2(sin(uv.y*3.+t),cos(uv.x*2.7-t*.8))*.035;
      float a=sin(q.x*5.8+q.y*4.1+t+sin(q.y*4.-t)*1.15);
      float b=sin(q.y*6.3-q.x*4.2-t*.7+sin(q.x*4.+t)*1.2);
      float c=sin(q.x*9.+q.y*7.+t*.6);
      float n=(a*.5+b*.35+c*.15)*.5+.5;
      float cloud=smoothstep(.08,.94,n);
      float gray=.985-.115*cloud+.018*exp(-pow(n-.61,2.)*23.);
      gray+=(grain(gl_FragCoord.xy+vec2(floor(time*24.),0.))-.5)*.018;
      vec2 drift=vec2(.5+.13*sin(time*.11),.52+.16*cos(time*.09));
      vec2 target=mix(drift,pointer,.38);
      vec2 offset=(uv-target)*vec2(resolution.x/resolution.y,1.);
      float radius=length(offset);
      float halo=exp(-radius*radius*6.2);
      float radialBias=dot(offset/max(radius,.0001),vec2(.66,.34));
      float phase=radius*1.6+radialBias*.035-time*.055;
      vec3 spectrum=.5+.5*cos(6.283185*(vec3(0.,.35,.7)+phase));
      vec3 color=vec3(gray)+spectrum*halo*.115;
      gl_FragColor=vec4(color,1.);
    }`;
  const compile = (type, source) => {
    const shader = gl.createShader(type);
    gl.shaderSource(shader, source);
    gl.compileShader(shader);
    if (!gl.getShaderParameter(shader, gl.COMPILE_STATUS)) { gl.deleteShader(shader); return null; }
    return shader;
  };
  const vs = compile(gl.VERTEX_SHADER, vertex), fs = compile(gl.FRAGMENT_SHADER, fragment);
  if (!vs || !fs) return;
  const program = gl.createProgram();
  gl.attachShader(program, vs); gl.attachShader(program, fs); gl.linkProgram(program);
  if (!gl.getProgramParameter(program, gl.LINK_STATUS)) return;
  gl.useProgram(program);
  const buffer = gl.createBuffer();
  gl.bindBuffer(gl.ARRAY_BUFFER, buffer);
  gl.bufferData(gl.ARRAY_BUFFER, new Float32Array([-1,-1,1,-1,-1,1,1,1]), gl.STATIC_DRAW);
  const position = gl.getAttribLocation(program, 'position');
  gl.enableVertexAttribArray(position);
  gl.vertexAttribPointer(position, 2, gl.FLOAT, false, 0, 0);
  const resolution = gl.getUniformLocation(program, 'resolution');
  const pointer = gl.getUniformLocation(program, 'pointer');
  const time = gl.getUniformLocation(program, 'time');
  const motionPreference = matchMedia('(prefers-reduced-motion: reduce)');
  let pageActive = true;
  let px = .5, py = .5, raf = 0;
  const resize = () => {
    const ratio = Math.min(devicePixelRatio || 1, 1.5);
    const width = Math.max(1, Math.round(innerWidth * ratio));
    const height = Math.max(1, Math.round(innerHeight * ratio));
    if (canvas.width !== width || canvas.height !== height) { canvas.width = width; canvas.height = height; }
    gl.viewport(0, 0, width, height);
    gl.uniform2f(resolution, width, height);
  };
  const onPointer = event => { px = event.clientX / innerWidth; py = 1 - event.clientY / innerHeight; };
  const render = now => {
    gl.uniform2f(pointer, px, py);
    gl.uniform1f(time, motionPreference.matches ? 0 : now * .001);
    gl.drawArrays(gl.TRIANGLE_STRIP, 0, 4);
  };
  const stop = () => {
    if (raf) cancelAnimationFrame(raf);
    raf = 0;
  };
  const canAnimate = () => pageActive && !document.hidden && !motionPreference.matches;
  const draw = now => {
    raf = 0;
    if (!canAnimate()) return;
    render(now);
    raf = requestAnimationFrame(draw);
  };
  const refresh = () => {
    stop();
    if (!pageActive || document.hidden) return;
    resize();
    render(performance.now());
    if (canAnimate()) raf = requestAnimationFrame(draw);
  };
  addEventListener('resize', refresh, { passive: true });
  addEventListener('pointermove', onPointer, { passive: true });
  addEventListener('pagehide', () => { pageActive = false; stop(); });
  addEventListener('pageshow', () => { pageActive = true; refresh(); });
  document.addEventListener('visibilitychange', refresh);
  motionPreference.addEventListener('change', refresh);
  refresh();
})();
