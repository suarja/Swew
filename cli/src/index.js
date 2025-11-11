import React from 'react';
import { render, Text } from 'ink';
import LoginCommand from './login.js';
import StatusCommand from './status.js';

process.env.NODE_TLS_REJECT_UNAUTHORIZED = process.env.SWEW_ACCEPT_SELF_SIGNED ? '0' : process.env.NODE_TLS_REJECT_UNAUTHORIZED ?? '0';

const [, , command] = process.argv;

const commands = {
    login: React.createElement(LoginCommand),
    status: React.createElement(StatusCommand),
    help: React.createElement(Text, null, 'swew login — start device-code flow\nswew status — call /api/profile with stored token\n'),
};

render(commands[command] ?? commands.help);
