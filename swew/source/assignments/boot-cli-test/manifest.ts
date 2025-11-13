import type {AssignmentManifest} from '../types.js';
import {runBootCliTest} from './run.js';

export const manifest: AssignmentManifest = {
	code: 'BOOT-CLI-TEST',
	title: 'CLI Fixture Assignment',
	description:
		'A smoke-test kit that mirrors the fixture course used in Symfony tests so we can exercise the submit flow end-to-end.',
	course: {slug: 'course-fixture', title: 'Fixture Course'},
	lesson: {slug: 'lesson-fixture', title: 'Fixture Lesson', sequence: 1},
	cliVersion: '0.2.0',
	kitVersion: 1,
	ritual: ['$ swew submit BOOT-CLI-TEST', '> gather environment info', '> upload results'],
	run: runBootCliTest,
};
