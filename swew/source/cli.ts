#!/usr/bin/env node
import meow from 'meow';
import {render, Box, Text} from 'ink';
import React from 'react';
import process from 'node:process';
import Login from './components/login.js';
import Status from './components/status.js';
import Courses from './components/courses.js';
import Submit from './components/submit.js';

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
  submit    Run a bundled assignment kit and upload results

Examples
  $ swew-cli login
  $ swew-cli status
  $ swew-cli submit BOOT-CLI-TEST
`,
	{
		importMeta: import.meta,
	},
);

const command = cli.input[0];

if (command === 'submit') {
	render(React.createElement(Submit, {code: cli.input[1]}));
} else {
	const views: Record<string, React.ReactElement> = {
		login: React.createElement(Login),
		status: React.createElement(Status),
		courses: React.createElement(Courses),
		help: React.createElement(
			Box,
			{flexDirection: 'column'},
			React.createElement(Text, null, 'Run "swew login", "swew status", "swew courses", or "swew submit"'),
		),
	};

	render(views[command ?? 'help']);
}
