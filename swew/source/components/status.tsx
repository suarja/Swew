import React, {useEffect, useState} from 'react';
import {Box, Text} from 'ink';
import fetch from 'node-fetch';
import {getBaseUrl, readConfig} from '../lib/config.js';

type Profile = {
	email: string;
	name: string;
	roles: string[];
};

export default function Status() {
	const [state, setState] = useState<{status: 'loading'} | {status: 'error'; message: string} | {status: 'ready'; profile: Profile}>({
		status: 'loading',
	});

	useEffect(() => {
		const run = async () => {
			const config = await readConfig();
			if (!config?.token) {
				setState({status: 'error', message: 'No token found. Run `swew-cli login` first.'});
				return;
			}

			try {
				const response = await fetch(`${getBaseUrl()}/api/profile`, {
					headers: {Authorization: `Bearer ${config.token}`},
				});

				if (!response.ok) {
					throw new Error(`Request failed (${response.status})`);
				}

				const profile = (await response.json()) as Profile;
				setState({status: 'ready', profile});
			} catch (error) {
				setState({status: 'error', message: (error as Error).message});
			}
		};

		void run();
	}, []);

	if (state.status === 'loading') {
		return <Text>Fetching profile…</Text>;
	}

	if (state.status === 'error') {
		return <Text color="red">status failed: {state.message}</Text>;
	}

	return (
		<Box flexDirection="column">
			<Text color="green">
				Authenticated as {state.profile.name} ({state.profile.email})
			</Text>
			<Text>Roles: {state.profile.roles.join(', ')}</Text>
		</Box>
	);
}
