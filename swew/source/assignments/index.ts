import type {AssignmentManifest} from './types.js';
import {manifest as bootCliTest} from './boot-cli-test/manifest.js';

export const ASSIGNMENT_MANIFESTS: AssignmentManifest[] = [bootCliTest];

export const findAssignmentManifest = (code: string): AssignmentManifest | undefined => {
	const normalized = code.trim().toUpperCase();

	return ASSIGNMENT_MANIFESTS.find(manifest => manifest.code.toUpperCase() === normalized);
};
