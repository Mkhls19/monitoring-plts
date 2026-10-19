#include <Wire.h>
#include <Adafruit_INA219.h>
#include <WiFi.h>
#include <HTTPClient.h>
#include <ArduinoJson.h>

// ========== KONFIGURASI WiFi ==========
const char* ssid = "abc";
const char* password = "15151515";

// ========== KONFIGURASI SERVER LARAVEL ==========
const char* serverUrl = "http://10.56.20.22:8000/api/data";

// ========== SENSOR ==========
Adafruit_INA219 ina219_input(0x40);
Adafruit_INA219 ina219_output(0x41);

// Variabel
float voltage_in = 0, current_in = 0, power_in = 0;
float voltage_out = 0, current_out = 0, power_out = 0;
float efficiency = 0;

const float VOLTAGE_THRESHOLD = 2.0;
const float MIN_POWER = 0.01;

void setup() {
  Serial.begin(115200);
  Wire.begin(21, 22);

  Serial.println("Inisialisasi sensor INA219...");

  if (!ina219_input.begin()) {
    Serial.println("GAGAL INA219 INPUT!");
    while (1);
  }

  if (!ina219_output.begin()) {
    Serial.println("GAGAL INA219 OUTPUT!");
    while (1);
  }

  ina219_input.setCalibration_32V_2A();
  ina219_output.setCalibration_32V_2A();

  Serial.println("Sensor siap 🔥");

  // WiFi
  WiFi.begin(ssid, password);
  Serial.print("Connecting WiFi");

  while (WiFi.status() != WL_CONNECTED) {
    delay(500);
    Serial.print(".");
  }

  Serial.println("\n✅ WiFi connected!");
  Serial.println(WiFi.localIP());
}

void loop() {

  // ===== BACA SENSOR =====
  float busVoltage_in = ina219_input.getBusVoltage_V();
  float current_in_mA = ina219_input.getCurrent_mA();

  float busVoltage_out = ina219_output.getBusVoltage_V();
  float current_out_mA = ina219_output.getCurrent_mA();

  voltage_in = busVoltage_in;
  voltage_out = busVoltage_out;

  current_in = current_in_mA / 1000.0;
  current_out = current_out_mA / 1000.0;

  // Filter noise
  if (voltage_in < VOLTAGE_THRESHOLD) {
    voltage_in = 0;
    current_in = 0;
  }

  if (voltage_out < VOLTAGE_THRESHOLD) {
    voltage_out = 0;
    current_out = 0;
  }

  power_in = voltage_in * current_in;
  power_out = voltage_out * current_out;

  if (power_in > MIN_POWER) {
    efficiency = (power_out / power_in) * 100.0;
    if (efficiency > 100) efficiency = 100;
  } else {
    efficiency = 0;
  }

  // ===== DEBUG SERIAL =====
  Serial.println("===== DATA =====");
  Serial.printf("IN: %.2f V | %.2f A | %.2f W\n", voltage_in, current_in, power_in);
  Serial.printf("OUT: %.2f V | %.2f A | %.2f W\n", voltage_out, current_out, power_out);
  Serial.printf("EFF: %.2f %%\n", efficiency);

  // ===== KIRIM KE LARAVEL =====
  if (WiFi.status() == WL_CONNECTED) {

    HTTPClient http;
    http.begin(serverUrl);
    http.addHeader("Content-Type", "application/json");

    StaticJsonDocument<256> doc;

    doc["voltage_in"] = voltage_in;
    doc["current_in"] = current_in;
    doc["power_in"] = power_in;
    doc["voltage_out"] = voltage_out;
    doc["current_out"] = current_out;
    doc["power_out"] = power_out;
    doc["efficiency"] = efficiency;

    String json;
    serializeJson(doc, json);

    int response = http.POST(json);

    if (response > 0) {
      Serial.print("✅ Sent: ");
      Serial.println(response);
    } else {
      Serial.print("❌ Error: ");
      Serial.println(response);
    }

    http.end();
  } else {
    Serial.println("⚠️ WiFi disconnected");
  }

  delay(5000);
}