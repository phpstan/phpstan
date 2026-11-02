import ko from '@tko/build.reference';
import type {PlaygroundViewModel} from './PlaygroundViewModel';
import type {PlaygroundTabViewModel} from './PlaygroundTabViewModel';
import {when} from './tsx';

type OptionName = Parameters<PlaygroundViewModel['isNonDefault']>[0];

const levels = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10'];

// The template of PlaygroundViewModel.
export function playgroundTemplate(vm: PlaygroundViewModel): JSX.Element {
	const errors = ko.pureComputed(() => vm.currentTab()?.errors ?? []);

	return (
		<div>
			{when(() => !vm.loaded(), () => (
				<div class="mx-auto w-12 h-12 my-12">
					<div class="spinner"></div>
				</div>
			))}

			{when(vm.loaded, () => (
				<>
					{optionsDialog(vm)}
					{fixDialog(vm)}

					<div
						id="playground-editor"
						ko-codeMirror={{
							code: vm.code,
							restoredState: vm.restoredEditorState,
							onXRayToggle: (enabled) => vm.onXRayToggle(enabled),
						}}
						ko-codeMirrorErrors={errors}
						ko-codeMirrorXRay={{enabled: vm.xrayEnabled, busy: vm.xrayBusy, tab: vm.currentTab}}
					>
					</div>

					{/* Options and level on the left, Fix and Share on the right. On phones Fix gets a row of its own below. */}
					<div class="flex flex-wrap items-center mt-4 gap-2 md:gap-6 px-4 md:px-0">
						<button ko-click={() => vm.openOptions()} class="inline-flex flex-1 md:flex-initial items-center px-2.5 py-3 rounded-lg text-md font-medium leading-4 bg-gray-100 hover:bg-gray-200 md:w-32 h-10 justify-center border-gray-300 border dark:border-black relative cursor-pointer">
							<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
								<path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" />
								<path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
							</svg>
							<span class="ml-1.5">Options</span>
							{when(() => vm.changedOptionsCount() > 0, () => (
								<span class="absolute -top-2 -right-2 inline-flex items-center justify-center min-w-5 h-5 px-1 text-xs font-bold text-white bg-blue-500 rounded-full" ko-text={vm.changedOptionsCount}></span>
							))}
						</button>
						<select class="block flex-1 md:flex-initial rounded-md border border-gray-300 dark:border-black" ko-value={vm.level}>
							{levels.map((level) => <option value={level}>Level {level}</option>)}
						</select>

						<div class="hidden md:block md:flex-1"></div>

						{when(() => vm.fixedCode() !== null, () => fixButton(vm))}

						{/* Hidden but still taking its place while the result is loading, so nothing jumps. */}
						<div class="flex flex-1 md:flex-initial" ko-css={{invisible: vm.isLoading}}>
							{when(() => vm.legacyResult() === null && !vm.isSharing(), () => (
								<a href="#" ko-click={() => vm.share()} class="inline-flex flex-1 md:flex-initial items-center px-2.5 py-3 rounded-lg text-md font-medium leading-4 bg-gray-100 hover:bg-gray-200 md:w-32 h-10 justify-center border-gray-300 border dark:border-black">
									<svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" class="w-6 h-6"><path d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"></path></svg>
									<span class="ml-2" ko-text={vm.shareText}></span>
								</a>
							))}

							{when(() => vm.legacyResult() === null && vm.isSharing(), () => (
								<span class="inline-flex flex-1 md:flex-initial items-center px-2.5 py-3 rounded-lg text-md font-medium leading-4 bg-gray-100 md:w-32 h-10 justify-center border-gray-300 border">
									{/* By Sam Herbert (@sherb), for everyone. More @ http://goo.gl/7AJzbL */}
									<svg width="120" height="30" viewBox="0 0 120 30" xmlns="http://www.w3.org/2000/svg" fill="#3f83f8" class="h-12 w-12">
										<circle cx="15" cy="15" r="15">
											<animate attributeName="r" from="15" to="15" begin="0s" dur="0.8s" values="15;9;15" calcMode="linear" repeatCount="indefinite" />
											<animate attributeName="fill-opacity" from="1" to="1" begin="0s" dur="0.8s" values="1;.5;1" calcMode="linear" repeatCount="indefinite" />
										</circle>
										<circle cx="60" cy="15" r="9" fill-opacity="0.3">
											<animate attributeName="r" from="9" to="9" begin="0s" dur="0.8s" values="9;15;9" calcMode="linear" repeatCount="indefinite" />
											<animate attributeName="fill-opacity" from="0.5" to="0.5" begin="0s" dur="0.8s" values=".5;1;.5" calcMode="linear" repeatCount="indefinite" />
										</circle>
										<circle cx="105" cy="15" r="15">
											<animate attributeName="r" from="15" to="15" begin="0s" dur="0.8s" values="15;9;15" calcMode="linear" repeatCount="indefinite" />
											<animate attributeName="fill-opacity" from="1" to="1" begin="0s" dur="0.8s" values="1;.5;1" calcMode="linear" repeatCount="indefinite" />
										</circle>
									</svg>
								</span>
							))}
						</div>
					</div>

					{when(vm.isLoading, () => (
						<div class="m-auto w-12 h-12 mt-8">
							<div class="spinner"></div>
						</div>
					))}

					{when(vm.hasServerError, () => (
						<span class="inline-flex text-red-500 mb-8">An error occurred. Please try again later.</span>
					))}

					{when(() => !vm.hasServerError(), () => (
						<div class="pt-4">
							{when(() => vm.upToDateTabs() !== null && !vm.isLoading(), () => (
								<div class="flex content-center justify-center">
									<div class="inline-block bg-yellow-100 dark:bg-yellow-500 dark:text-white rounded-lg px-2 py-1 text-sm">This result is out of date. <a href="#" class="text-blue-700 dark:text-blue-500" ko-click={() => vm.showUpToDateTabs()}>Show the latest result</a></div>
								</div>
							))}

							{when(() => vm.tabs().length > 1 && !vm.isLoading() && vm.legacyResult() === null, () => (
								<nav class="flex mt-4 border-b border-gray-400">
									{ko.pureComputed(() => vm.tabs().map((tab, index) => {
										// The shown tab, also after tabs that were shown before come back, like a snippet's.
										const active = ko.pureComputed(() => vm.currentTab() === tab);
										return <a href="#" class="relative px-3 py-2 font-medium text-md mr-4 leading-5 rounded-md rounded-b-none text-gray-500 border-l border-t border-r" ko-click={() => vm.switchTab(index)} ko-class={ko.pureComputed(() => tab.tabClass(active()))} ko-style={{top: ko.pureComputed(() => active() ? '1px' : '0')}}>{tab.title}</a>;
									}))}
								</nav>
							))}

							{when(() => vm.currentTab() !== null && !vm.isLoading() && vm.legacyResult() === null, () => tabResult(vm, vm.currentTab()!))}

							{when(() => vm.legacyResult() !== null && !vm.isLoading(), () => (
								<pre ko-html={vm.legacyResult} class="bg-gray-800 w-auto p-4 mt-4 leading-none"></pre>
							))}
						</div>
					))}
				</>
			))}
		</div>
	);
}

function tabResult(vm: PlaygroundViewModel, tab: PlaygroundTabViewModel): JSX.Element {
	return (
		<div>
			<div class="flex items-stretch mx-4 md:mx-0">
				{tab.errors.length > 0 && vm.tabs().length < 2 ? <span class="inline-flex flex-grow justify-center md:flex-grow-0 items-center px-4 py-3 h-12 rounded-lg text-lg font-medium leading-4 bg-red-100 text-red-900 mt-4 dark:border dark:border-current">{tab.errorsText}</span> : null}

				{tab.errors.length === 0 ? <span class="inline-flex flex-grow justify-center md:flex-grow-0 items-center px-4 py-3 h-12 rounded-lg text-lg font-medium leading-4 bg-green-100 text-green-900 mt-4 dark:border dark:border-current">No errors!</span> : null}
			</div>
			{tab.errors.length > 0
				? (
						<table class="w-full mt-8">
							<thead>
								<tr>
									<th class="px-6 py-3 border-b border-gray-400 bg-gray-100 text-left text-base leading-4 font-medium text-gray-800 tracking-wider">Line</th>
									<th class="px-6 py-3 border-b border-gray-400 bg-gray-100 text-left text-base leading-4 font-medium text-gray-800 tracking-wider">Error</th>
								</tr>
							</thead>
							<tbody class="bg-white">
								{tab.errors.map((error) => (
									<tr>
										<td class="px-6 py-4 whitespace-nowrap border-b border-gray-200 text-base leading-5 font-medium text-gray-500">{error.line}</td>
										<td class="px-6 py-4 border-b border-gray-200 text-base leading-5 text-gray-900">
											<div class="flex items-center">
												<div class="flex-shrink">
													<div style="white-space: preserve;">{error.message}</div>
													{error.tip !== undefined ? <div class="mt-2 text-gray-500 text-sm" ko-html={vm.linkify(error.tip)}></div> : null}
												</div>

												<div class="flex flex-shrink-0 items-center gap-2 ml-2 md:flex-grow md:justify-end md:ml-4">
													{error.fixable === true ? <span class="fixable-pill" title="PHPStan can fix this error, use the Fix button above the results">{wrenchIcon('w-3 h-3')}<span class="hidden md:inline">Fixable</span></span> : null}
													{error.identifier !== undefined ? <a target="_blank" class="inline-flex items-center justify-center w-6 h-6 ml-1 rounded-full bg-blue-500 hover:bg-blue-600 text-white text-xs font-bold leading-none" href={'/error-identifiers/' + error.identifier}>?</a> : null}
												</div>
											</div>
										</td>
									</tr>
								))}
							</tbody>
						</table>
					)
				: null}
		</div>
	);
}

function fixButton(vm: PlaygroundViewModel): JSX.Element {
	const label = ko.pureComputed(() => {
		const count = vm.currentTab()?.fixableCount ?? 0;
		if (count === 0) {
			return 'Fix';
		}
		return `Fix ${count} ${count === 1 ? 'error' : 'errors'}`;
	});
	return (
		<button id="fix-button" type="button" ko-click={() => vm.openFix()} class="fix-button order-last basis-full md:order-none md:basis-auto">
			{wrenchIcon('w-5 h-5')}
			<span ko-text={label}></span>
		</button>
	);
}

function fixDialog(vm: PlaygroundViewModel): JSX.Element {
	// Only the diff scrolls: the title and the buttons stay in view, also on phones.
	return (
		<dialog id="fixDialog" class="fix-dialog rounded-xl shadow-2xl border border-gray-200 p-0 w-[calc(100%-1rem)] md:w-[calc(100%-2rem)] max-w-5xl" ko-click={(_, event) => vm.handleFixBackdropClick(vm, event)}>
			<div class="flex flex-col max-h-[calc(100dvh-1rem)] md:max-h-[85vh] p-4 md:p-6">
				<div class="flex items-start justify-between gap-4 mb-3 md:mb-4">
					<div class="flex items-center gap-3">
						<span class="fix-dialog-icon">{wrenchIcon('w-5 h-5')}</span>
						<div>
							<h2 class="text-lg font-semibold leading-6">Fix errors</h2>
							<p class="hidden md:block text-sm text-gray-500">Review the changes before they are applied to the code in the editor.</p>
						</div>
					</div>
					<button type="button" ko-click={() => vm.closeFix()} class="text-gray-400 hover:text-gray-600 p-1 cursor-pointer" aria-label="Close">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
							<path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
						</svg>
					</button>
				</div>

				<div class="fix-diff" ko-codeMirrorDiff={vm.fixDiff}></div>

				<div class="flex flex-col md:flex-row md:items-center gap-3 md:gap-4 mt-3 md:mt-5">
					<p class="text-xs text-gray-500 md:flex-1">
						Fix your own project the same way: run <code class="px-1 py-0.5 bg-gray-100 rounded font-mono">phpstan analyse --fix</code>, and add <code class="px-1 py-0.5 bg-gray-100 rounded font-mono">--fix-ignored</code> to fix ignored errors too.
						{' '}<a href="/user-guide/command-line-usage#--fix%2C---fix-ignored" target="_blank" class="underline hover:no-underline">Learn more</a>
					</p>
					<div class="flex gap-2 md:gap-3">
						<button type="button" ko-click={() => vm.closeFix()} class="flex-1 md:flex-initial px-5 py-2 rounded-lg text-sm font-medium border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 cursor-pointer">Cancel</button>
						<button type="button" ko-click={() => vm.applyFix()} class="flex-1 md:flex-initial px-5 py-2 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 cursor-pointer">Apply</button>
					</div>
				</div>
			</div>
		</dialog>
	);
}

function wrenchIcon(className: string): JSX.Element {
	return (
		<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor" class={className} aria-hidden="true">
			<path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75a4.5 4.5 0 0 1-4.884 4.484c-1.076-.091-2.264.071-2.95.904l-7.152 8.684a2.548 2.548 0 1 1-3.586-3.586l8.684-7.152c.833-.686.995-1.874.904-2.95a4.5 4.5 0 0 1 6.336-4.486l-3.276 3.276a3.004 3.004 0 0 0 2.25 2.25l3.276-3.276c.256.565.398 1.192.398 1.852Z" />
			<path stroke-linecap="round" stroke-linejoin="round" d="M4.867 19.125h.008v.008h-.008v-.008Z" />
		</svg>
	);
}

function optionsDialog(vm: PlaygroundViewModel): JSX.Element {
	return (
		<dialog id="optionsDialog" class="options-dialog rounded-xl shadow-2xl border border-gray-200 p-0 w-[calc(100%-2rem)] max-w-4xl backdrop:bg-black/50" ko-click={(_, event) => vm.handleOptionsBackdropClick(vm, event)}>
			<div class="p-6">
				<div class="flex items-center justify-between mb-4">
					<div class="flex items-center gap-3">
						<h2 class="text-lg font-semibold">Options</h2>
						<a href="/config-reference" target="_blank" class="inline-flex items-center text-sm text-blue-600 hover:text-blue-800 hover:underline">Config reference{externalLinkIcon('w-4 h-4 ml-1')}</a>
					</div>
					<button type="button" ko-click={() => vm.cancelOptions()} class="text-gray-400 hover:text-gray-600 p-1 cursor-pointer">
						<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
							<path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
						</svg>
					</button>
				</div>

				<div class="mb-5">
					<button type="button" ko-click={() => vm.resetOptionsToDefaults()} ko-enable={vm.isNotDefaults} class="text-sm px-3 py-1.5 rounded-md border border-gray-300 bg-white hover:bg-gray-50 text-gray-700 disabled:bg-gray-50 disabled:text-gray-400 disabled:border-gray-200 disabled:cursor-default cursor-pointer">Reset to defaults</button>
				</div>

				<div class="flex flex-col md:flex-row md:gap-12">
					<fieldset class="md:flex-1 border-t border-gray-200 pt-4 md:border-t-0 md:pt-0">
						<legend class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">General</legend>
						<div class="space-y-3">
							{checkboxOption(vm, 'strictRules', 'Strict rules', 'stricter-analysis')}
							{checkboxOption(vm, 'bleedingEdge', 'Bleeding edge', 'bleeding-edge')}
							{checkboxOption(vm, 'treatPhpDocTypesAsCertain', 'Treat PHPDoc types as certain', 'treatphpdoctypesascertain')}
							{checkboxOption(vm, 'inferPrivatePropertyTypeFromConstructor', 'Infer private property type from constructor', 'inferprivatepropertytypefromconstructor')}
							{checkboxOption(vm, 'rememberPossiblyImpureFunctionValues', 'Remember possibly impure function values', 'rememberpossiblyimpurefunctionvalues')}
							{checkboxOption(vm, 'checkBenevolentUnionTypes', 'Check benevolent union types', 'checkbenevolentuniontypes')}
							{checkboxOption(vm, 'checkTooWideTypesInProtectedAndPublicMethods', 'Check too wide types in non-private methods', 'checktoowidereturntypesinprotectedandpublicmethods')}
							<div>
								<div class="flex items-center gap-1.5 mb-1.5">
									<span class="text-sm font-medium" ko-css={optionLabelCss(vm, 'reportUnsafeArrayStringKeyCasting')}>Array String Keys</span>
									<a href="/blog/why-array-string-keys-are-not-type-safe" target="_blank" class="text-gray-400 hover:text-gray-600" ko-click={followWithoutBubbling}>{externalLinkIcon('w-3.5 h-3.5')}</a>
								</div>
								<div class="flex gap-4 ml-0.5">
									{radioOption(vm, 'null', 'Unsafe')}
									{radioOption(vm, 'detect', 'Detect')}
									{radioOption(vm, 'prevent', 'Prevent')}
								</div>
							</div>
						</div>
					</fieldset>

					<fieldset class="md:flex-1 mt-4 pt-4 border-t border-gray-200 md:mt-0 md:pt-0 md:border-t-0">
						<legend class="text-sm font-semibold text-gray-500 uppercase tracking-wide mb-3">Exceptions</legend>
						<div class="space-y-3">
							{checkboxOption(vm, 'implicitThrows', ['Implicit ', throwsTag()], 'exceptions.implicitthrows')}
							{checkboxOption(vm, 'reportUncheckedExceptionDeadCatch', 'Report unchecked exception dead catch', 'exceptions.reportuncheckedexceptiondeadcatch')}
							{checkboxOption(vm, 'missingCheckedExceptionInThrows', ['Missing checked exception in ', throwsTag()], 'exceptions.check.missingcheckedexceptioninthrows')}

							{/* Note: show only when missingCheckedExceptionInThrows or reportUncheckedExceptionDeadCatch are on */}
							<div>
								<label for="uncheckedExceptionClasses" class="block text-sm font-medium mb-1" ko-css={optionLabelCss(vm, 'uncheckedExceptionClasses')}>Unchecked exception classes</label>
								<textarea id="uncheckedExceptionClasses" ko-value={vm.uncheckedExceptionClasses} ko-valueUpdate="input" rows="2" placeholder={'One class per line, e.g.\n\\LogicException'} class="w-full text-sm rounded-md border-gray-300 font-mono dark:border-current"></textarea>
							</div>
							<div>
								<label for="checkedExceptionClasses" class="block text-sm font-medium mb-1" ko-css={optionLabelCss(vm, 'checkedExceptionClasses')}>Checked exception classes</label>
								<textarea id="checkedExceptionClasses" ko-value={vm.checkedExceptionClasses} ko-valueUpdate="input" rows="2" placeholder={'One class per line, e.g.\n\\RuntimeException'} class="w-full text-sm rounded-md border-gray-300 font-mono dark:border-current"></textarea>
							</div>

							{checkboxOption(vm, 'tooWideImplicitThrowType', ['Too wide implicit ', throwsTag(), ' type'], 'exceptions.check.toowideimplicitthrowtype')}
						</div>
					</fieldset>
				</div>

				<div class="mt-6 pt-5 border-t border-gray-200">
					<button type="button" ko-click={() => vm.applyOptions()} class="w-full md:w-auto px-5 py-2 rounded-lg text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 cursor-pointer">Apply changes</button>
				</div>
			</div>
		</dialog>
	);
}

function checkboxOption(vm: PlaygroundViewModel, option: OptionName, label: string | (string | JSX.Element)[], configReferenceAnchor: string): JSX.Element {
	return (
		<label class="flex items-center gap-2.5 cursor-pointer">
			<input type="checkbox" ko-checked={vm[option]} class="border-gray-300 rounded dark:border-current" />
			<span class="flex items-center gap-1.5" ko-css={{'text-blue-600': vm.isNonDefault(option)}}>{label}{configReferenceLink(configReferenceAnchor)}</span>
		</label>
	);
}

function radioOption(vm: PlaygroundViewModel, value: string, label: string): JSX.Element {
	return (
		<label class="flex items-center gap-1.5 cursor-pointer">
			<input type="radio" name="reportUnsafeArrayStringKeyCasting" ko-checked={vm.reportUnsafeArrayStringKeyCasting} value={value} class="border-gray-300 dark:border-current" />
			<span>{label}</span>
		</label>
	);
}

function optionLabelCss(vm: PlaygroundViewModel, option: OptionName): Record<string, ko.PureComputed<boolean>> {
	const nonDefault = vm.isNonDefault(option);
	return {'text-blue-600': nonDefault, 'text-gray-700': ko.pureComputed(() => !nonDefault())};
}

function throwsTag(): JSX.Element {
	return <code class="text-xs px-1 py-0.5 bg-gray-100 rounded font-mono">@throws</code>;
}

function configReferenceLink(anchor: string): JSX.Element {
	return <a href={`/config-reference#${anchor}`} target="_blank" class="text-gray-400 hover:text-gray-600" ko-click={followWithoutBubbling}>{externalLinkIcon('w-3.5 h-3.5')}</a>;
}

// Follows the link without the click bubbling up to the label and the dialog.
function followWithoutBubbling(_: unknown, event: MouseEvent): boolean {
	event.stopPropagation();
	return true;
}

function externalLinkIcon(className: string): JSX.Element {
	return <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" class={className}><path d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>;
}
