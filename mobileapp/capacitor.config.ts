import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
    appId: 'com.xashpos.co.zw',
    appName: 'xashpos',
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
