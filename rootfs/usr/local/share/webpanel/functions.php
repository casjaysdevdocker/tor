<?php

function getServiceStatus($service) {
    $output = shell_exec("pgrep -f '$service' 2>/dev/null");
    return !empty(trim($output));
}

function getServicePid($service) {
    $output = shell_exec("pgrep -f '$service' 2>/dev/null");
    return trim($output) ?: 'N/A';
}

function startService($service) {
    $init_script = "/usr/local/etc/docker/init.d/*$service*.sh";
    $files = glob($init_script);
    if (!empty($files)) {
        $script = $files[0];
        shell_exec("bash '$script' > /dev/null 2>&1 &");
        return true;
    }
    return false;
}

function stopService($service) {
    $pid = getServicePid($service);
    if ($pid !== 'N/A') {
        shell_exec("kill $pid 2>/dev/null");
        return true;
    }
    return false;
}

function restartService($service) {
    stopService($service);
    sleep(2);
    return startService($service);
}

function getLogTail($logfile, $lines = 50) {
    if (file_exists($logfile)) {
        return shell_exec("tail -n $lines '$logfile' 2>/dev/null");
    }
    return "Log file not found: $logfile";
}

function getConfigContent($configfile) {
    if (file_exists($configfile)) {
        return file_get_contents($configfile);
    }
    return '';
}

function saveConfigContent($configfile, $content) {
    return file_put_contents($configfile, $content) !== false;
}

function getHiddenServices() {
    $services = [];
    $services_dir = '/data/tor/server/services';
    
    if (is_dir($services_dir)) {
        $dirs = glob($services_dir . '/*', GLOB_ONLYDIR);
        foreach ($dirs as $dir) {
            $name = basename($dir);
            $hostname_file = $dir . '/hostname';
            $hostname = file_exists($hostname_file) ? trim(file_get_contents($hostname_file)) : 'Not generated yet';
            $services[] = [
                'name' => $name,
                'hostname' => $hostname,
                'path' => $dir
            ];
        }
    }
    
    return $services;
}

function createHiddenService($name, $port_mapping) {
    $services_dir = '/data/tor/server/services';
    $service_dir = $services_dir . '/' . $name;
    
    if (!is_dir($service_dir)) {
        mkdir($service_dir, 0700, true);
    }
    
    $config_content = "HiddenServiceDir $service_dir\n";
    $config_content .= "HiddenServicePort $port_mapping\n";
    
    $config_file = "/config/tor/server/hidden.d/$name.conf";
    return file_put_contents($config_file, $config_content) !== false;
}

function deleteHiddenService($name) {
    $services_dir = '/data/tor/server/services';
    $service_dir = $services_dir . '/' . $name;
    $config_file = "/config/tor/server/hidden.d/$name.conf";
    
    $success = true;
    if (is_dir($service_dir)) {
        $success &= shell_exec("rm -rf '$service_dir'") !== false;
    }
    if (file_exists($config_file)) {
        $success &= unlink($config_file);
    }
    
    return $success;
}

function getSystemStats() {
    $stats = [];
    
    $uptime = trim(shell_exec('uptime -p 2>/dev/null || echo "N/A"'));
    $stats['uptime'] = $uptime;
    
    $memory = shell_exec('free -m | grep "Mem:" | awk \'{print $3"/"$2" MB"}\'');
    $stats['memory'] = trim($memory) ?: 'N/A';
    
    $disk = shell_exec('df -h / | tail -1 | awk \'{print $3"/"$2" ("$5")"}\'');
    $stats['disk'] = trim($disk) ?: 'N/A';
    
    $load = trim(shell_exec('uptime | awk -F"load average:" \'{print $2}\' | awk \'{print $1}\' | tr -d ","'));
    $stats['load'] = $load ?: 'N/A';
    
    return $stats;
}

function sanitizeInput($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validatePortMapping($mapping) {
    return preg_match('/^\d+\s+\d+\.\d+\.\d+\.\d+:\d+$/', $mapping);
}

function validateServiceName($name) {
    return preg_match('/^[a-zA-Z0-9_-]+$/', $name);
}

?>