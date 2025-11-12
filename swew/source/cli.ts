#!/usr/bin/env node
import meow from 'meow';
import {render, Box, Text} from 'ink';
import React from 'react';
import process from 'node:process';
import Login from './components/login.js';
import Status from './components/status.js';
import Courses from './components/courses.js';

if (process.env['SWEW_ACCEPT_SELF_SIGNED'] === '1') {
	process.env['NODE_TLS_REJECT_UNAUTHORIZED'] = '0';
}

const cli = meow(
	`
Usage
  $ swew-cli <command>

Commands
  login     Start device-code authentication
  status    Call /api/profile using stored token
  courses   Fetch the DB-backed course catalog

Examples
  $ swew-cli login
  $ swew-cli status
`,
	{
		importMeta: import.meta,
	},
);

const command = cli.input[0];

const views: Record<string, React.ReactElement> = {
	login: React.createElement(Login),
	status: React.createElement(Status),
	courses: React.createElement(Courses),
	help: React.createElement(
		Box,
		{flexDirection: 'column'},
		React.createElement(Text, null, 'Run "swew login", "swew status", or "swew courses"'),
	),
};

render(views[command ?? 'help']);
