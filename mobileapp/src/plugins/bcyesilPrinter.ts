import { Printer } from '@bcyesil/capacitor-plugin-printer'

export interface PrintOptions {
    content: string
    name?: string
    orientation?: 'portrait' | 'landscape'
}

export async function printHelloWorld(): Promise<void> {
    const content = `
        <div style="text-align: center; font-family: monospace; padding: 20px;">
            <h1 style="font-size: 24px; margin-bottom: 10px;">HELLO WORLD</h1>
            <p style="font-size: 14px;">JX-532AP Test Print</p>
            <p style="font-size: 12px; margin-top: 20px;">${new Date().toLocaleString()}</p>
        </div>
    `

    await Printer.print({
        content,
        name: 'hello-world-test',
        orientation: 'portrait',
    })
}

export async function printText(text: string): Promise<void> {
    const content = `
        <div style="text-align: center; font-family: monospace; padding: 10px;">
            <pre style="font-size: 12px; white-space: pre-wrap;">${text}</pre>
        </div>
    `

    await Printer.print({
        content,
        name: 'text-print',
        orientation: 'portrait',
    })
}

export { Printer }
