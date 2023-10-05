<?php

class CustomDateTime extends DateTime implements JsonSerializable{
    // Override the constructor to set the microseconds to zero
    public string $date;
    public function __construct($datetime = "now", $timezone = null) {
        parent::__construct($datetime, $timezone);
        $this->setTime($this->format('H'), $this->format('i'), $this->format('s'), 0);
        // Override date to exclude the microseconds character
        $this->date = $this->format('Y-m-d H:i:s');
    }

    // Override the format method to exclude the microseconds character
    public function format($format): string
    {
        $format = str_replace('u', '', $format); // remove the 'u' character from the format string
        return parent::format($format);
    }

    // Override the setTime method to ignore the microseconds parameter
    public function setTime($hour, $minute, $second = 0, $microsecond = 0): DateTime
    {
        return parent::setTime($hour, $minute, $second, 0); // set the microseconds to zero
    }

    /**
     * @return array
     */
    public function jsonSerialize(): array
    {
        return [
            'date' => $this->date,
            'timezone_type' => $this->getTimezone()->getName(),
            'timezone' => $this->getTimezone()->getOffset($this)
        ];
    }
}
