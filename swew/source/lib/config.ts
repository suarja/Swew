import os from 'os';
import path from 'path';
import {promises as fs} from 'fs';

const CONFIG_DIR = path.join(os.homedir(), '.swew');
const CONFIG_FILE = path.join(CONFIG_DIR, 'config.json');

type Config = {
	token: string;
	lastVerifiedAt?: string;
};

export async function saveConfig(data: Config): Promise<void> {
	await fs.mkdir(CONFIG_DIR, {recursive: true});
	await fs.writeFile(CONFIG_FILE, JSON.stringify(data, null, 2), 'utf8');
}

export async function readConfig(): Promise<Config | null> {
	try {
		const raw = await fs.readFile(CONFIG_FILE, 'utf8');
		return JSON.parse(raw) as Config;
	} catch (error) {
		return null;
	}
}

export function getBaseUrl(): string {
	return process.env['SWEW_BASE_URL'] ?? 'https://localhost';
}
