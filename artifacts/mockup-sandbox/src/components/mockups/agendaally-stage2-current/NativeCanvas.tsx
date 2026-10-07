import React, { useEffect, useRef } from "react";
// @ts-expect-error This is the original untyped JavaScript animation source copied verbatim.
import { Gradient } from "./gradient.js";

export const NativeCanvas = () => {
  const canvasRef = useRef<HTMLCanvasElement | null>(null);
  const gradient = new Gradient();

  useEffect(() => {
    gradient.initGradient("#gradient-canvas");
  }, [gradient]);

  return <canvas id="gradient-canvas" className="absolute" ref={canvasRef} data-transition-in />;
};