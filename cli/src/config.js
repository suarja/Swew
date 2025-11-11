import os from 'os';
import path from 'path';
import { promises as fs } from 'fs';

const CONFIG_DIR = path.join(os.homedir(), '.swew');
const CONFIG_PATH = path.join(CONFIG_DIR, 'config.json');

export async function saveToken(token, profile) {
    await fs.mkdir(CONFIG_DIR, { recursive: true });
    await fs.writeFile(CONFIG_PATH, JSON.stringify({ token, profile }, null, 2), 'utf8');
}

export async function readConfig() {
    try {
        const raw = await fs.readFile(CONFIG_PATH, 'utf8');
        return JSON.parse(raw);
    } catch (error) {
        return null;
    }
}

export function getBaseUrl() {
    return process.env.SWEW_BASE_URL ?? 'https://localhost';
}
