import React, {useEffect, useState} from 'react';
import {Box, Text} from 'ink';
import fetch from 'node-fetch';
import {getBaseUrl, readConfig} from '../lib/config.js';

type LessonSummary = {
	slug: string;
	title: string;
	summary?: string | null;
	sequence: number;
};

type Course = {
	slug: string;
	title: string;
	summary?: string | null;
	status: string;
	lessonCount: number;
	lessons: LessonSummary[];
};

type ReadyState = {
	status: 'ready';
	courses: Course[];
};

export default function Courses() {
	const [state, setState] = useState<
		| {status: 'loading'}
		| {status: 'error'; message: string}
		| ReadyState
	>({status: 'loading'});

	useEffect(() => {
		const run = async () => {
			const config = await readConfig();
			if (!config?.token) {
				setState({status: 'error', message: 'No token found. Run `swew-cli login` first.'});
				return;
			}

			try {
				const response = await fetch(`${getBaseUrl()}/api/courses`, {
					headers: {Authorization: `Bearer ${config.token}`},
				});

				if (!response.ok) {
					throw new Error(`Request failed (${response.status})`);
				}

				const payload = (await response.json()) as {courses: Course[]};
				setState({status: 'ready', courses: payload.courses});
			} catch (error) {
				setState({status: 'error', message: (error as Error).message});
			}
		};

		void run();
	}, []);

	if (state.status === 'loading') {
		return <Text>Loading course catalog…</Text>;
	}

	if (state.status === 'error') {
		return <Text color="red">courses failed: {state.message}</Text>;
	}

	if (state.courses.length === 0) {
		return <Text>No live courses yet. Seed content in /admin.</Text>;
	}

	return (
		<Box flexDirection="column">
			<Text color="cyan">Course catalog ({state.courses.length})</Text>
			{state.courses.map(course => (
				<Box key={course.slug} flexDirection="column" marginTop={1}>
					<Text>
						<Text color="green">[{course.status.toUpperCase()}]</Text> {course.title}
					</Text>
					{course.summary ? (
						<Text dimColor>{course.summary}</Text>
					) : (
						<Text dimColor>Summary coming soon.</Text>
					)}
					{course.lessons.length > 0 ? (
						course.lessons.map(lesson => (
							<Text key={lesson.slug}>
								<Text color="gray">  - {lesson.sequence.toString().padStart(2, '0')} · </Text>
								{lesson.title}
							</Text>
						))
					) : (
						<Text dimColor>  - Lessons will appear once published.</Text>
					)}
				</Box>
			))}
		</Box>
	);
}
