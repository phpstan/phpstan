export interface PHPStanError {
	message: string,
	line: number,
	tip?: string,
	identifier?: string,
	ignorable?: boolean,
}
