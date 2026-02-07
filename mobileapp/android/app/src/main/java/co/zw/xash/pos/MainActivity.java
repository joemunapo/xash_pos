package co.zw.xash.pos;

import android.os.Bundle;
import com.getcapacitor.BridgeActivity;

public class MainActivity extends BridgeActivity {
    @Override
    public void onCreate(Bundle savedInstanceState) {
        // Register custom plugins
        registerPlugin(SunmiScannerPlugin.class);

        super.onCreate(savedInstanceState);
    }
}
