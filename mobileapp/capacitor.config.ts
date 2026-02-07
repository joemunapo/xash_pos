import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
    appId: 'co.zw.xash.pos',
    appName: 'XASH Pos',
    webDir: 'dist',

    server: {
        androidScheme: 'https',
    },

    plugins: {
        PushNotifications: {
            presentationOptions: ["badge", "sound", "alert"]
        },
        SunmiPrinter: {
            bindOnLoad: true
        }
    }
};

export default config;
