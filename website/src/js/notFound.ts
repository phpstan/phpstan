// The 404 page is standalone (it does not use the site layout/nav), so it
// pulls in the Inter font itself. The @font-face is imported here rather than
// referenced from a stylesheet so Vite fingerprints the woff2 files -- the
// same pattern used in MainMenuViewModel.ts for the rest of the site.
import '@fontsource-variable/inter/index.css';
