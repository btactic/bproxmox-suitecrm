<?php

function bytes($bytes, $force_unit = NULL, $show_unit = true, $format = NULL, $si = TRUE) {
    // Format string
    $format = ($format === NULL) ? ($show_unit ? '%01.3f %s' : '%01.3f') : (string) $format;

    // IEC prefixes (binary)
    if ($si == FALSE OR strpos($force_unit, 'i') !== FALSE) {
        $units = array('B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB');
        $mod   = 1024;
    } else { // SI prefixes (decimal)
        $units = array('B', 'kB', 'MB', 'GB', 'TB', 'PB');
        $mod   = 1000;
    }

    // Determine unit to use
    if (($power = array_search((string) $force_unit, $units)) === FALSE) {
        $power = ($bytes > 0) ? floor(log($bytes, $mod)) : 0;
    }

    return $show_unit ? sprintf($format, $bytes / pow($mod, $power), $units[$power])
        : sprintf($format, $bytes / pow($mod, $power));
}

function bytes_to_gibibytes($bytes) {
    return bytes($bytes, 'GiB', false);
}

function to_gigabytes($size_info) {
    $size = 0;
    if (preg_match('/^([0-9]+(\.[0-9]+)?)([MGTP])$/', $size_info, $size_info)) {
        switch ($size_info[3]) {
            case 'M':
                $size = $size_info[1] / 1024.0;
                break;
            case 'G':
                $size = $size_info[1];
                break;
            case 'T':
                $size = $size_info[1] * 1024;
                break;
            case 'P':
                $size = $size_info[1] * 1024 * 1024;
                break;
        }
    }
    return sprintf('%.3f', $size);
}

?>
