<?php
// JSON-Datei zum Speichern der Shellys in FPP
$pluginConfigFile = $mediaDirectory . "/config/plugin.fpp-shelly.json";

if (!file_exists($pluginConfigFile)) {
    file_put_contents($pluginConfigFile, json_encode([]));
}
?>

<div id="fpp-shelly" class="settings">
    <h2>Shelly Geräteverwaltung</h2>
    
    <!-- Formular zum Hinzufügen -->
    <div class="row">
        <div class="col-md-4">
            <input type="text" id="shelly_name" class="form-control" placeholder="Gerätename (z.B. Lichterkette)">
        </div>
        <div class="col-md-4">
            <input type="text" id="shelly_ip" class="form-control" placeholder="IP-Adresse (z.B. 192.168.1.100)">
        </div>
        <div class="col-md-2">
            <input type="number" id="shelly_relay" class="form-control" value="0" min="0" placeholder="Relais">
        </div>
        <div class="col-md-2">
            <button class="btn btn-success" onclick="addShelly()">Hinzufügen</button>
        </div>
    </div>

    <hr>

    <!-- Tabelle der konfigurierten Shellys -->
    <table class="table table-striped" id="shellyTable">
        <thead>
            <tr>
                <th>Name</th>
                <th>IP-Adresse</th>
                <th>Relais</th>
                <th>Aktionen / Testen</th>
                <th>Löschen</th>
            </tr>
        </thead>
        <tbody>
            <!-- WIRD PER JS BEFÜLLT -->
        </tbody>
    </table>
</div>

<script>
let shellyConfigFile = 'plugin.fpp-shelly.json';

function loadShellys() {
    GetPluginJSON(shellyConfigFile, function(data) {
        let tbody = $('#shellyTable tbody');
        tbody.empty();
        $.each(data, function(index, dev) {
            tbody.append(`
                <tr>
                    <td><b>${dev.name}</b></td>
                    <td>${dev.ip}</td>
                    <td>${dev.relay}</td>
                    <td>
                        <button class="btn btn-sm btn-primary" onclick="testShelly('${dev.ip}', 'on', ${dev.relay})">EIN</button>
                        <button class="btn btn-sm btn-secondary" onclick="testShelly('${dev.ip}', 'off', ${dev.relay})">AUS</button>
                        <button class="btn btn-sm btn-warning" onclick="testShelly('${dev.ip}', 'toggle', ${dev.relay})">TOGGLE</button>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-danger" onclick="deleteShelly(${index})">X</button>
                    </td>
                </tr>
            `);
        });
    });
}

function testShelly(ip, action, relay) {
    // Verwendet den FPP Command Endpoint
    let args = [ip, action, relay.toString()];
    RunCommand("Shelly Control", args, function(data) {
        $.jGrowl("Befehl gesendet: " + action + " an " + ip, { theme: 'success' });
    });
}

function addShelly() {
    let name = $('#shelly_name').val();
    let ip = $('#shelly_ip').val();
    let relay = $('#shelly_relay').val() || 0;

    if (!name || !ip) {
        alert("Bitte Name und IP angeben.");
        return;
    }

    GetPluginJSON(shellyConfigFile, function(data) {
        if (!Array.isArray(data)) data = [];
        data.push({ name: name, ip: ip, relay: parseInt(relay) });
        SavePluginJSON(shellyConfigFile, JSON.stringify(data), function() {
            loadShellys();
            $('#shelly_name').val('');
            $('#shelly_ip').val('');
        });
    });
}

function deleteShelly(index) {
    GetPluginJSON(shellyConfigFile, function(data) {
        data.splice(index, 1);
        SavePluginJSON(shellyConfigFile, JSON.stringify(data), function() {
            loadShellys();
        });
    });
}

$(document).ready(function() {
    loadShellys();
});
</script>
