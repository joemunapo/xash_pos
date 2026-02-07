package co.zw.xash.pos;

import android.content.BroadcastReceiver;
import android.content.Context;
import android.content.Intent;
import android.content.IntentFilter;
import android.os.Build;
import android.util.Log;

import com.getcapacitor.JSObject;
import com.getcapacitor.Plugin;
import com.getcapacitor.PluginCall;
import com.getcapacitor.PluginMethod;
import com.getcapacitor.annotation.CapacitorPlugin;

@CapacitorPlugin(name = "SunmiScanner")
public class SunmiScannerPlugin extends Plugin {

    private static final String TAG = "SunmiScanner";

    // Sunmi scanner broadcast actions
    private static final String ACTION_DATA_CODE_RECEIVED = "com.sunmi.scanner.ACTION_DATA_CODE_RECEIVED";
    private static final String DATA = "data";
    private static final String SOURCE = "source_byte";

    private BroadcastReceiver scanReceiver;
    private boolean isListening = false;

    @PluginMethod
    public void startListening(PluginCall call) {
        if (isListening) {
            call.resolve();
            return;
        }

        try {
            scanReceiver = new BroadcastReceiver() {
                @Override
                public void onReceive(Context context, Intent intent) {
                    String action = intent.getAction();
                    if (ACTION_DATA_CODE_RECEIVED.equals(action)) {
                        String barcode = intent.getStringExtra(DATA);
                        if (barcode != null && !barcode.isEmpty()) {
                            Log.d(TAG, "Barcode scanned: " + barcode);

                            JSObject result = new JSObject();
                            result.put("barcode", barcode);
                            result.put("source", "sunmi_hardware");

                            notifyListeners("onBarcodeScanned", result);
                        }
                    }
                }
            };

            IntentFilter filter = new IntentFilter();
            filter.addAction(ACTION_DATA_CODE_RECEIVED);

            if (Build.VERSION.SDK_INT >= Build.VERSION_CODES.TIRAMISU) {
                getContext().registerReceiver(scanReceiver, filter, Context.RECEIVER_EXPORTED);
            } else {
                getContext().registerReceiver(scanReceiver, filter);
            }

            isListening = true;
            Log.d(TAG, "Started listening for Sunmi scanner broadcasts");
            call.resolve();

        } catch (Exception e) {
            Log.e(TAG, "Error starting scanner listener", e);
            call.reject("Failed to start listening: " + e.getMessage());
        }
    }

    @PluginMethod
    public void stopListening(PluginCall call) {
        if (scanReceiver != null && isListening) {
            try {
                getContext().unregisterReceiver(scanReceiver);
                isListening = false;
                Log.d(TAG, "Stopped listening for Sunmi scanner broadcasts");
            } catch (Exception e) {
                Log.e(TAG, "Error stopping scanner listener", e);
            }
        }
        scanReceiver = null;
        call.resolve();
    }

    @PluginMethod
    public void isSunmiDevice(PluginCall call) {
        boolean isSunmi = false;

        try {
            String manufacturer = Build.MANUFACTURER;
            String brand = Build.BRAND;
            String model = Build.MODEL;

            Log.d(TAG, "Device info - Manufacturer: " + manufacturer + ", Brand: " + brand + ", Model: " + model);

            // Check if it's a Sunmi device
            if (manufacturer != null && manufacturer.toLowerCase().contains("sunmi")) {
                isSunmi = true;
            } else if (brand != null && brand.toLowerCase().contains("sunmi")) {
                isSunmi = true;
            } else if (model != null && model.toLowerCase().contains("sunmi")) {
                isSunmi = true;
            }

            // Also check for common Sunmi models
            if (model != null) {
                String modelLower = model.toLowerCase();
                if (modelLower.contains("v2") || modelLower.contains("v1") ||
                    modelLower.contains("t2") || modelLower.contains("t1") ||
                    modelLower.contains("p2") || modelLower.contains("l2")) {
                    // Could be a Sunmi device, check manufacturer
                    if (manufacturer != null && (
                        manufacturer.toLowerCase().contains("sunmi") ||
                        manufacturer.toLowerCase().contains("商米"))) {
                        isSunmi = true;
                    }
                }
            }

        } catch (Exception e) {
            Log.e(TAG, "Error checking if Sunmi device", e);
        }

        JSObject result = new JSObject();
        result.put("isSunmi", isSunmi);
        call.resolve(result);
    }

    @Override
    protected void handleOnDestroy() {
        if (scanReceiver != null && isListening) {
            try {
                getContext().unregisterReceiver(scanReceiver);
            } catch (Exception e) {
                Log.e(TAG, "Error unregistering receiver on destroy", e);
            }
        }
        super.handleOnDestroy();
    }
}
