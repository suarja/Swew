import React, {useEffect, useState} from 'react';
import {Box, Text} from 'ink';
import fetch from 'node-fetch';
import {getBaseUrl, saveConfig} from '../lib/config.js';

type DevicePayload = {
	device_code: string;
	user_code: string;
	verification_uri: string;
	interval: number;
	expires_in: number;
};

export default function Login() {
	const [state, setState] = useState<
		| {status: 'request'}
		| {status: 'wait'; payload: DevicePayload}
		| {status: 'error'; message: string}
		| {status: 'success'}
	>({status: 'request'});

	useEffect(() => {
		const requestCode = async () => {
			try {
				const response = await fetch(`${getBaseUrl()}/api/device-code`, {method: 'POST'});
				if (!response.ok) {
					throw new Error(`Request failed (${response.status})`);
				}

				const payload = (await response.json()) as DevicePayload;
				setState({status: 'wait', payload});
				await poll(payload);
			} catch (error) {
				setState({status: 'error', message: (error as Error).message});
			}
		};

		const poll = async (payload: DevicePayload): Promise<void> => {
			try {
				const response = await fetch(`${getBaseUrl()}/api/device-token`, {
					method: 'POST',
					headers: {'content-type': 'application/json'},
					body: JSON.stringify({device_code: payload.device_code}),
				});

				if (response.status === 202) {
					const {interval} = (await response.json()) as {interval: number};
					setTimeout(() => {
						void poll(payload);
					}, (interval ?? payload.interval) * 1000);
					return;
				}

				const data = (await response.json()) as {access_token?: string; error?: string};

				if (!response.ok || !data.access_token) {
					throw new Error(data.error ?? 'authorization_pending');
				}

				await saveConfig({
					token: data.access_token,
					lastVerifiedAt: new Date().toISOString(),
				});

				setState({status: 'success'});
			} catch (error) {
				setState({status: 'error', message: (error as Error).message});
			}
		};

		void requestCode();
	}, []);

	if (state.status === 'request') {
		return (
			<Box flexDirection="column">
				<Text>Requesting device code…</Text>
			</Box>
		);
	}

	if (state.status === 'error') {
		return (
			<Box flexDirection="column">
				<Text color="red">Login failed: {state.message}</Text>
			</Box>
		);
	}

	if (state.status === 'success') {
		return (
			<Box flexDirection="column">
				<Text color="green">Device approved. Token saved to ~/.swew/config.json</Text>
			</Box>
		);
	}

	return (
		<Box flexDirection="column">
			<Text>Go to {state.payload.verification_uri}</Text>
			<Text>
				Enter code: <Text color="green">{state.payload.user_code}</Text>
			</Text>
			<Text>Waiting for confirmation… (Ctrl+C to cancel)</Text>
		</Box>
	);
}
