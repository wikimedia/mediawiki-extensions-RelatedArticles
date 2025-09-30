declare namespace Codex {
	export interface CodexSuggestionThumbnail {
		width: number;
		height: number;
		url: string;
	}

	export interface ListTitleObject {
		label: string;
		url: string;
		description: string;
		thumbnail?: CodexSuggestionThumbnail;
	}
}
