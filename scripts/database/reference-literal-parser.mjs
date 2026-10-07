// Static PHP literal extraction for the review package. Never evaluates PHP,
// loads Laravel, invokes a seeder, connects to a database or reads dotenv.
export function literalArray(source, marker, occurrence = 0) {
  let offset = -1;
  for (let n = 0; n <= occurrence; n++) {
    offset = source.indexOf(marker, offset + 1);
    if (offset < 0) throw new Error(`Missing literal marker: ${marker}`);
  }
  const beginning = source.slice(offset + marker.length).match(/\[|\barray\s*\(/);
  if (!beginning) throw new Error(`Missing array: ${marker}`);
  let position = offset + marker.length + beginning.index;
  const tokenPattern = /\s+|\/\/[^\n]*|\/\*[\s\S]*?\*\/|'(?:\\.|[^'\\])*'|"(?:\\.|[^"\\])*"|=>|::|-?\d+(?:\.\d+)?|[A-Za-z_][A-Za-z_0-9\\]*|[\[\],.+*/()-]/y;
  let pending;
  function token() {
    if (pending !== undefined) { const value = pending; pending = undefined; return value; }
    while (true) {
      tokenPattern.lastIndex = position;
      const match = tokenPattern.exec(source);
      if (!match) throw new Error(`Unsupported nonliteral PHP near ${marker}, offset ${position}`);
      position = tokenPattern.lastIndex;
      if (/^\s|^\/\/|^\/\*/.test(match[0])) continue;
      return match[0];
    }
  }
  function peek() { const value = token(); pending = value; return value; }
  function atom() {
    const value = token();
    if (value === "[") return array();
    if (value === "array") {
      if (token() !== "(") throw new Error("Malformed literal array");
      return array(")");
    }
    if (value === "(") {
      const result = expression();
      if (token() !== ")") throw new Error("Unbalanced literal expression");
      return result;
    }
    if (value[0] === "'") return value.slice(1, -1).replace(/\\(['\\])/g, "$1");
    if (value[0] === '"') return JSON.parse(value);
    if (/^-?\d/.test(value)) return Number(value);
    if (value === "null") return null;
    if (value === "true" || value === "false") return value === "true";
    throw new Error(`Executable PHP rejected in literal array: ${value}`);
  }
  function expression() {
    let value = atom();
    while (["*", "/"].includes(peek())) {
      const operator = token();
      const other = atom();
      if (typeof value !== "number" || typeof other !== "number") throw new Error("Nonnumeric expression");
      value = operator === "*" ? value * other : value / other;
    }
    while (peek() === ".") {
      token();
      value = String(value) + String(atom());
    }
    return value;
  }
  function array(terminator = "]") {
    const entries = [];
    let associative = false;
    while (peek() !== terminator) {
      const first = expression();
      if (peek() === "=>") {
        token(); associative = true;
        entries.push([first, expression()]);
      } else entries.push([entries.length, first]);
      const end = token();
      if (end === terminator) { pending = terminator; break; }
      if (end !== ",") throw new Error(`Unexpected array delimiter: ${end}`);
    }
    token();
    if (associative && new Set(entries.map(([key]) => String(key))).size !== entries.length) {
      throw new Error("Duplicate literal key");
    }
    return associative ? Object.fromEntries(entries) : entries.map(([, value]) => value);
  }
  const first = token();
  if (first === "array") {
    if (token() !== "(") throw new Error("Array expected");
    return array(")");
  }
  if (first !== "[") throw new Error("Array expected");
  return array();
}
