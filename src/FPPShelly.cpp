#include <fpp-pch.h>
#include <iostream>
#include <memory>
#include <string>
#include <vector>
#include <curl/curl.h>

#include "commands/CommandRepository.h"
#include "plugin.h"

// Hilfsfunktion: HTTP Request an Shelly (Gen 1 REST / Gen 2 Kompatibilität)
static void sendShellyCommand(const std::string& ip, const std::string& action, int relay = 0) {
    CURL* curl = curl_easy_init();
    if (!curl) return;

    std::string url;
    if (action == "toggle") {
        url = "http://" + ip + "/relay/" + std::to_string(relay) + "?turn=toggle";
    } else if (action == "on") {
        url = "http://" + ip + "/relay/" + std::to_string(relay) + "?turn=on";
    } else {
        url = "http://" + ip + "/relay/" + std::to_string(relay) + "?turn=off";
    }

    curl_easy_setopt(curl, CURLOPT_URL, url.c_str());
    curl_easy_setopt(curl, CURLOPT_TIMEOUT, 3L);
    curl_easy_setopt(curl, CURLOPT_NOBODY, 1L); // Nur HTTP Request senden, Body ignorieren
    
    curl_easy_perform(curl);
    curl_easy_cleanup(curl);
}

// FPP 10.0+ Kompatibler Command
class ShellyCommand : public Command {
public:
    ShellyCommand() : Command("Shelly Control") {
        args.push_back(CommandArg("IP", "string", "IP-Adresse des Shelly Geräts"));
        args.push_back(CommandArg("Action", "string", "Aktion (on/off/toggle)").setOptions({"on", "off", "toggle"}));
        args.push_back(CommandArg("Relay", "int", "Relais Index").setDefaultValue("0"));
    }

    virtual std::unique_ptr<Command::Result> run(const std::vector<std::string>& args) override {
        if (args.size() < 2) {
            return std::make_unique<Command::ErrorResult>("Shelly Command benötigt mindestens IP und Aktion.");
        }

        std::string ip = args[0];
        std::string action = args[1];
        int relay = (args.size() > 2 && !args[2].empty()) ? std::stoi(args[2]) : 0;

        sendShellyCommand(ip, action, relay);

        return std::make_unique<Command::Result>("Shelly Signal gesendet an " + ip);
    }
};

// Plugin Einstiegspunkt für FPP 10+
class FPPShellyPlugin : public FPPPlugin {
public:
    FPPShellyPlugin() : FPPPlugin("fpp-shelly") {
        CommandRepository::instance().addCommand(new ShellyCommand());
    }

    virtual ~FPPShellyPlugin() {}
};

extern "C" {
    FPPPlugin* createPlugin() {
        return new FPPShellyPlugin();
    }
}
