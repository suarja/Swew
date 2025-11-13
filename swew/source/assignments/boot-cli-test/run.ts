import type {AssignmentCheck, AssignmentRunResult} from '../types.js';

const MIN_NODE_MAJOR = 18;

const checkNodeVersion = (): AssignmentCheck => {
	const version = process.versions.node ?? '0.0.0';
	const major = Number.parseInt(version.split('.')[0] ?? '0', 10);
	const status: 'pass' | 'fail' = Number.isNaN(major) || major < MIN_NODE_MAJOR ? 'fail' : 'pass';

	return {
		id: 'node-version',
		status,
		details: `Detected ${version}`,
	};
};

export async function runBootCliTest(): Promise<AssignmentRunResult> {
	const checks: AssignmentCheck[] = [checkNodeVersion(), {id: 'cwd', status: 'pass', details: process.cwd()}];
	const status: AssignmentRunResult['status'] = checks.every(check => check.status === 'pass') ? 'pass' : 'fail';

	return {status, checks};
}
