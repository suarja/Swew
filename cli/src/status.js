import React, { useEffect, useState } from 'react';
import { Box, Text } from 'ink';
import fetch from 'node-fetch';
import { getBaseUrl, readConfig } from './config.js';

const baseUrl = getBaseUrl();

const StatusCommand = () => {
    const [state, setState] = useState({ loading: true });

    useEffect(() => {
        const run = async () => {
            const config = await readConfig();
            if (!config?.token) {
                setState({ error: 'No token found. Run "npm run login" first.' });
                return;
            }

            try {
                const response = await fetch(`${baseUrl}/api/profile`, {
                    headers: {
                        Authorization: `Bearer ${config.token}`,
                    },
                });

                if (!response.ok) {
                    const data = await response.json().catch(() => ({}));
                    throw new Error(data.error ?? `Request failed (${response.status})`);
                }

                const profile = await response.json();
                setState({ profile });
            } catch (error) {
                setState({ error: error.message });
            }
        };

        run();
    }, []);

    if (state.loading) {
        return React.createElement(Text, null, 'Fetching profile…');
    }

    if (state.error) {
        return React.createElement(Text, { color: 'red' }, `status failed: ${state.error}`);
    }

    return React.createElement(
        Box,
        { flexDirection: 'column' },
        React.createElement(Text, { color: 'green' }, `Authenticated as ${state.profile.name} (${state.profile.email})`),
        React.createElement(Text, null, `Roles: ${state.profile.roles.join(', ')}`),
    );
};

export default StatusCommand;
