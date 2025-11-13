import React, {useEffect, useState} from 'react';
import {Box, Text} from 'ink';
import fetch from 'node-fetch';
import os from 'node:os';
import process from 'node:process';
import {createRequire} from 'node:module';
import {getBaseUrl, readConfig} from '../lib/config.js';
import type {AssignmentManifest, AssignmentRunResult} from '../assignments/types.js';
import {ASSIGNMENT_MANIFESTS, findAssignmentManifest} from '../assignments/index.js';

const require = createRequire(import.meta.url);
const pkg = require('../../package.json') as {version?: string};

type Props = {
	code?: string;
};

type LoadingState =
	| {status: 'loading'}
	| {status: 'running'; phase: string; assignment: string}
	| {status: 'error'; message: string}
	| {status: 'done'; assignment: string; result: AssignmentRunResult; server: SubmissionResponse};

type SubmissionResponse = {
	assignment: string;
	status: string;
	nextAssignment?: {code: string; title?: string} | null;
};

const cliVersion = pkg.version ?? '0.0.0';

const systemInfo = {
	os: `${os.type()} ${os.release()}`,
	node: process.version,
	arch: os.arch(),
};

async function ensureOkay(response: Awaited<ReturnType<typeof fetch>>, context: string): Promise<void> {
	if (response.ok) {
		return;
	}

	const statusText = response.statusText || 'Request failed';
	if (response.status === 401) {
		throw new Error(`${context} failed (401 Unauthorized). Run \`swew login\` and try again.`);
	}

	const contentType = response.headers.get('content-type') ?? '';
	let body = await response.text();

	if (contentType.includes('application/json')) {
		try {
			const parsed = JSON.parse(body) as {error?: string; message?: string};
			body = parsed.error ?? parsed.message ?? '';
		} catch {
			// keep original body
		}
	} else {
		body = body.replace(/<style[\s\S]*?<\/style>/gi, '')
			.replace(/<script[\s\S]*?<\/script>/gi, '')
			.replace(/<[^>]+>/g, ' ')
			.replace(/\s+/g, ' ')
			.trim();
	}

	const details = body ? ` ${body.slice(0, 200)}${body.length > 200 ? '…' : ''}` : '';
	throw new Error(`${context} failed (${response.status} ${statusText}).${details}`);
}

async function fetchProgress(token: string): Promise<{nextAssignment?: {code: string}}> {
	const response = await fetch(`${getBaseUrl()}/api/progress`, {
		headers: {Authorization: `Bearer ${token}`},
	});

	await ensureOkay(response, 'Fetching progress');

	return (await response.json()) as {nextAssignment?: {code: string}};
}

async function submitResult(
	token: string,
	manifest: AssignmentManifest,
	result: AssignmentRunResult,
): Promise<SubmissionResponse> {
	const response = await fetch(`${getBaseUrl()}/api/submissions`, {
		method: 'POST',
		headers: {
			Authorization: `Bearer ${token}`,
			'content-type': 'application/json',
		},
		body: JSON.stringify({
			assignment: manifest.code,
			status: result.status === 'pass' ? 'pass' : 'fail',
			cliVersion,
			kitVersion: manifest.kitVersion,
			checks: result.checks,
			prompts: {},
			system: systemInfo,
			logs: result.artifacts?.map(artifact => `${artifact.label}: ${artifact.path}`).join('\n') ?? '',
		}),
	});

	await ensureOkay(response, `Submitting ${manifest.code}`);

	return (await response.json()) as SubmissionResponse;
}

export default function Submit({code}: Props) {
	const [state, setState] = useState<LoadingState>({status: 'loading'});

	useEffect(() => {
		const run = async () => {
			const config = await readConfig();
			if (!config?.token) {
				setState({status: 'error', message: 'No token found. Run `swew login` first.'});
				return;
			}

			let assignmentCode = code?.toUpperCase();
			if (!assignmentCode) {
				const progress = await fetchProgress(config.token);
				if (!progress.nextAssignment) {
					setState({status: 'error', message: 'All assignments cleared. Specify a code to re-run kits.'});
					return;
				}

				assignmentCode = progress.nextAssignment.code;
			}

			const manifest = findAssignmentManifest(assignmentCode);
			if (!manifest) {
				const knownCodes = ASSIGNMENT_MANIFESTS.length > 0 ? ASSIGNMENT_MANIFESTS.map(current => current.code).join(', ') : 'none bundled';
				setState({
					status: 'error',
					message: `Assignment ${assignmentCode} not bundled in this CLI build. Available kits: ${knownCodes}`,
				});
				return;
			}

			setState({status: 'running', assignment: manifest.code, phase: 'Executing local evaluator'});
			const result = await manifest.run();

			setState({status: 'running', assignment: manifest.code, phase: 'Uploading submission'});
			const server = await submitResult(config.token, manifest, result);

			setState({status: 'done', assignment: manifest.code, result, server});
		};

		run().catch(error => {
			setState({status: 'error', message: (error as Error).message});
		});
	}, [code]);

	if (state.status === 'loading') {
		return <Text>Preparing submission…</Text>;
	}

	if (state.status === 'running') {
		return (
			<Box flexDirection="column">
				<Text color="cyan">
					{state.assignment} · {state.phase}
				</Text>
			</Box>
		);
	}

	if (state.status === 'error') {
		return (
			<Box flexDirection="column">
				<Text color="red">submit failed</Text>
				<Text>- {state.message}</Text>
				<Text>- Run `swew login` again if the error mentions authorization.</Text>
				<Text>- Specify a bundled code (e.g. `swew submit BOOT-CLI-TEST`) if no kits are listed.</Text>
			</Box>
		);
	}

	return (
		<Box flexDirection="column">
			<Text color={state.result.status === 'pass' ? 'green' : 'red'}>
				Local result: {state.result.status.toUpperCase()}
			</Text>
			{state.result.checks.map(check => (
				<Text key={check.id}>
					<Text color={check.status === 'pass' ? 'green' : 'red'}>[{check.status.toUpperCase()}]</Text> {check.id}{' '}
					{check.details ? `· ${check.details}` : ''}
				</Text>
			))}
			<Box marginTop={1} flexDirection="column">
				<Text color="cyan">
					Server status: {state.server.status.toUpperCase()} ({state.server.assignment})
				</Text>
				{state.server.nextAssignment ? (
					<Text>
						Next assignment unlocked: {state.server.nextAssignment.code}
						{state.server.nextAssignment.title ? ` · ${state.server.nextAssignment.title}` : ''}
					</Text>
				) : (
					<Text>Next assignment unavailable (course complete or server pending).</Text>
				)}
			</Box>
		</Box>
	);
}
