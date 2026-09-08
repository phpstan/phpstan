// "AST X-Ray" payload produced by the playground runner (playground-runner/xray.php).
//
// strings: every kind, type, property name and rendered scalar, interned.
// nodes:   [kind, from, to, parent, type, props] in pre-order with UTF-16
//          offsets; kind and type index into strings (type -1 when PHPStan has
//          none), parent is -1 at the root. props is a flat
//          [name, value, name, value, ...] list: name indexes into strings,
//          value is a child node index (>= 0), a list of those, or
//          -(stringIndex + 1) for a rendered scalar.
export interface XRayData {
	strings: string[];
	nodes: [number, number, number, number, number, (number | number[])[]][];
}
