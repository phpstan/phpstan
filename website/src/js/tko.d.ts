// TKO (Technical Knockout) 4 is the runtime we bundle, but it ships no
// TypeScript declarations. Its public API is compatible with Knockout 3, so we
// borrow the type definitions from the `knockout` package, which is kept as a
// dev-only dependency purely for this purpose (it is never bundled at runtime).
declare module '@tko/build.knockout' {
	import ko from 'knockout';
	export default ko;
}
