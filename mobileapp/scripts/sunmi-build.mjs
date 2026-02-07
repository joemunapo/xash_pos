import { execSync } from 'node:child_process'
import { readFileSync, writeFileSync } from 'node:fs'
import { dirname, resolve } from 'node:path'
import { fileURLToPath } from 'node:url'

const __dirname = dirname(fileURLToPath(import.meta.url))
const appRoot = resolve(__dirname, '..')
const androidDir = resolve(appRoot, 'android')
const gradlePath = resolve(androidDir, 'app', 'build.gradle')

const args = new Set(process.argv.slice(2))
const shouldBump = args.has('--bump')
const dryRun = args.has('--dry-run')

function nowDateParts() {
    const now = new Date()
    const yyyy = String(now.getFullYear())
    const mm = String(now.getMonth() + 1).padStart(2, '0')
    const dd = String(now.getDate()).padStart(2, '0')
    return { yyyy, mm, dd }
}

function toVersionCode() {
    const { yyyy, mm, dd } = nowDateParts()
    return Number(`${yyyy}${mm}${dd}`)
}

function toVersionName() {
    const { yyyy, mm, dd } = nowDateParts()
    return `${yyyy}.${mm}.${dd}`
}

function updateAndroidVersion() {
    const versionCode = toVersionCode()
    const versionName = toVersionName()
    const before = readFileSync(gradlePath, 'utf8')
    const hasVersionCode = /versionCode\s+\d+/.test(before)
    const hasVersionName = /versionName\s+"[^"]+"/.test(before)

    if (!hasVersionCode || !hasVersionName) {
        throw new Error('Could not find versionCode/versionName in android/app/build.gradle')
    }

    const next = before
        .replace(/versionCode\s+\d+/, `versionCode ${versionCode}`)
        .replace(/versionName\s+"[^"]+"/, `versionName "${versionName}"`)

    if (next !== before) {
        writeFileSync(gradlePath, next, 'utf8')
        console.log(`[sunmi-build] Android version bumped to code=${versionCode}, name=${versionName}`)
        return
    }

    console.log(`[sunmi-build] Android version already set to code=${versionCode}, name=${versionName}`)
}

function run(command, cwd = appRoot) {
    console.log(`[sunmi-build] ${command}`)
    if (dryRun) return
    execSync(command, { cwd, stdio: 'inherit', shell: true })
}

if (shouldBump) {
    updateAndroidVersion()
}

run('npm run build')
run('npx cap sync android')
run('./gradlew assembleRelease', androidDir)
