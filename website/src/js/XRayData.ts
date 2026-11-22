// "AST X-Ray" payload produced by the playground runner (playground-runner/xray.php).
//
// strings: every kind, type, property name and rendered scalar, interned.
// nodes:   [kind, from, to, parent, type, props, effects?] in pre-order with
//          UTF-16 offsets; kind and type index into strings (type -1 when
//          PHPStan has none), parent is -1 at the root. props is a flat
//          [name, value, name, value, ...] list: name indexes into strings,
//          value is a child node index (>= 0), a list of those, or
//          -(stringIndex + 1) for a rendered scalar. effects is
//          [throwPoints, impurePoints], two lists of string indexes, present
//          only when the expression has any.
// effects: whether expressions carry effects at all; payloads from before
//          they were recorded don't, and there a missing list means unknown.
export interface XRayData {
	strings: string[];
	effects?: boolean;
	nodes: [number, number, number, number, number, (number | number[])[], [number[], number[]]?][];
}
