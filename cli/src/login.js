import React, { useEffect, useState } from 'react';
import { Box, Text } from 'ink';
import fetch from 'node-fetch';
import { getBaseUrl, saveToken } from './config.js';

const baseUrl = getBaseUrl();

const LoginCommand = () => {
    const [step, setStep] = useState('request');
    const [error, setError] = useState(null);
    const [payload, setPayload] = useState(null);

    useEffect(() => {
        const requestCode = async () => {
            try {
                const response = await fetch(`${baseUrl}/api/device-code`, {
                    method: 'POST',
                    headers: { 'content-type': 'application/json' },
                });
                if (!response.ok) {
                    throw new Error(`Request failed (${response.status})`);
                }
                const data = await response.json();
                setPayload(data);
                setStep('wait');
                pollForApproval(data);
            } catch (err) {
                setError(err.message);
                setStep('error');
            }
        };

        requestCode();
    }, []);

    const pollForApproval = async (credentials) => {
        try {
            const response = await fetch(`${baseUrl}/api/device-token`, {
                method: 'POST',
                headers: { 'content-type': 'application/json' },
                body: JSON.stringify({ device_code: credentials.device_code }),
            });

            if (response.status === 202) {
                const data = await response.json();
                setTimeout(() => pollForApproval(credentials), (data.interval ?? 5) * 1000);
                return;
            }

            const data = await response.json();

            if (!response.ok) {
                setError(data.error ?? 'Unknown error');
                setStep('error');
                return;
            }

            await saveToken(data.access_token, { verification_uri: credentials.verification_uri });
            setStep('success');
        } catch (err) {
            setError(err.message);
            setStep('error');
        }
    };

    if (step === 'request') {
        return React.createElement(
            Box,
            { flexDirection: 'column' },
            React.createElement(Text, null, 'Requesting device code…'),
        );
    }

    if (step === 'error') {
        return React.createElement(
            Box,
            { flexDirection: 'column' },
            React.createElement(Text, { color: 'red' }, `Login failed: ${error}`),
        );
    }

    if (step === 'success') {
        return React.createElement(
            Box,
            { flexDirection: 'column' },
            React.createElement(Text, { color: 'green' }, 'Device approved. Token saved to ~/.swew/config.json'),
        );
    }

    return React.createElement(
        Box,
        { flexDirection: 'column' },
        React.createElement(Text, null, `Go to ${payload?.verification_uri}`),
        React.createElement(Text, null, `Enter code: ${payload?.user_code ?? ''}`),
        React.createElement(Text, null, 'Waiting for confirmation… (Ctrl+C to cancel)'),
    );
};

export default LoginCommand;
