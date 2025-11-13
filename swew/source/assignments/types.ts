export type AssignmentCheck = {
	id: string;
	status: 'pass' | 'fail';
	details?: string;
};

export type AssignmentRunResult = {
	status: 'pass' | 'fail';
	checks: AssignmentCheck[];
	artifacts?: Array<{label: string; path: string}>;
};

export type AssignmentManifest = {
	code: string;
	title: string;
	description: string;
	course: {slug: string; title: string};
	lesson: {slug: string; title: string; sequence: number};
	cliVersion: string;
	kitVersion: number;
	ritual: string[];
	run: () => Promise<AssignmentRunResult>;
};
