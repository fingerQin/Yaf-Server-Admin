<?php

namespace finger\Image\Driver;

/*
 * :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
 * ::
 * :: GIFDecoder Version 2.0 by László Zsidi, http://gifs.hu
 * ::
 * :: Created at 2007. 02. 01. '07.47.AM'
 * ::
 * ::
 * ::
 * ::
 * :: Try on-line GIFBuilder Form demo based on GIFDecoder.
 * ::
 * :: http://gifs.hu/phpclasses/demos/GifBuilder/
 * ::
 * :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
 */
class GIFDecoder {

    private $GIF_buffer = [];

    private $GIF_arrays = [];

    private $GIF_delays = [];

    private $GIF_stream = '';

    private $GIF_string = '';

    private $GIF_bfseek = 0;

    private $GIF_screen = [];

    private $GIF_global = [];

    private $GIF_sorted;

    private $GIF_colorS;

    private $GIF_colorC;

    private $GIF_colorF;

    /*
     * :::::::::::::::::::::::::::::::::::::::::::::::::::
     * ::
     * :: GIFDecoder ( $GIF_pointer )
     * ::
     */
    public function __construct($GIF_pointer) {
        $this->GIF_stream = $GIF_pointer;

        $this->GIFGetByte(6); // GIF89a
        $this->GIFGetByte(7); // Logical Screen Descriptor

        $this->GIF_screen = $this->GIF_buffer;
        $this->GIF_colorF = $this->GIF_buffer[4] & 0x80 ? 1 : 0;
        $this->GIF_sorted = $this->GIF_buffer[4] & 0x08 ? 1 : 0;
        $this->GIF_colorC = $this->GIF_buffer[4] & 0x07;
        $this->GIF_colorS = 2 << $this->GIF_colorC;

        if ($this->GIF_colorF == 1) {
            $this->GIFGetByte(3 * $this->GIF_colorS);
            $this->GIF_global = $this->GIF_buffer;
        }

        for($cycle = 1; $cycle;) {
            if ($this->GIFGetByte(1)) {
                switch ($this->GIF_buffer[0]) {
                    case 0x21 :
                        $this->GIFReadExtensions();
                        break;
                    case 0x2C :
                        $this->GIFReadDescriptor();
                        break;
                    case 0x3B :
                        $cycle = 0;
                        break;
                }
            } else {
                $cycle = 0;
            }
        }
    }

    /*
     * :::::::::::::::::::::::::::::::::::::::::::::::::::
     * ::
     * :: GIFReadExtension ( )
     * ::
     */
    private function GIFReadExtensions() {
        $this->GIFGetByte(1);
        for(;;) {
            $this->GIFGetByte(1);
            if (($u = $this->GIF_buffer[0]) == 0x00) {
                break;
            }
            $this->GIFGetByte($u);
            if ($u == 4) {
                $this->GIF_delays[] = ($this->GIF_buffer[1] | $this->GIF_buffer[2] << 8);
            }
        }
    }

    /*
     * :::::::::::::::::::::::::::::::::::::::::::::::::::
     * ::
     * :: GIFReadExtension ( )
     * ::
     */
    private function GIFReadDescriptor() {
        $GIF_screen = [];

        $this->GIFGetByte(9);
        $GIF_screen = $this->GIF_buffer;
        $GIF_colorF = $this->GIF_buffer[8] & 0x80 ? 1 : 0;
        if ($GIF_colorF) {
            $GIF_code = $this->GIF_buffer[8] & 0x07;
            $GIF_sort = $this->GIF_buffer[8] & 0x20 ? 1 : 0;
        } else {
            $GIF_code = $this->GIF_colorC;
            $GIF_sort = $this->GIF_sorted;
        }
        $GIF_size = 2 << $GIF_code;
        $this->GIF_screen[4] &= 0x70;
        $this->GIF_screen[4] |= 0x80;
        $this->GIF_screen[4] |= $GIF_code;
        if ($GIF_sort) {
            $this->GIF_screen[4] |= 0x08;
        }
        $this->GIF_string = "GIF87a";
        $this->GIFPutByte($this->GIF_screen);
        if ($GIF_colorF == 1) {
            $this->GIFGetByte(3 * $GIF_size);
            $this->GIFPutByte($this->GIF_buffer);
        } else {
            $this->GIFPutByte($this->GIF_global);
        }
        $this->GIF_string .= chr(0x2C);
        $GIF_screen[8] &= 0x40;
        $this->GIFPutByte($GIF_screen);
        $this->GIFGetByte(1);
        $this->GIFPutByte($this->GIF_buffer);
        for(;;) {
            $this->GIFGetByte(1);
            $this->GIFPutByte($this->GIF_buffer);
            if (($u = $this->GIF_buffer[0]) == 0x00) {
                break;
            }
            $this->GIFGetByte($u);
            $this->GIFPutByte($this->GIF_buffer);
        }
        $this->GIF_string .= chr(0x3B);
        $this->GIF_arrays[] = $this->GIF_string;
    }

    private function GIFGetByte($len) {
        $this->GIF_buffer = [];
        for($i = 0; $i < $len; $i ++) {
            if ($this->GIF_bfseek > strlen($this->GIF_stream)) {
                return 0;
            }
            $this->GIF_buffer[] = ord($this->GIF_stream{$this->GIF_bfseek ++});
        }
        return 1;
    }

    /*
     * :::::::::::::::::::::::::::::::::::::::::::::::::::
     * ::
     * :: GIFPutByte ( $bytes )
     * ::
     */
    private function GIFPutByte($bytes) {
        for($i = 0; $i < count($bytes); $i ++) {
            $this->GIF_string .= chr($bytes[$i]);
        }
    }

    /*
     * :::::::::::::::::::::::::::::::::::::::::::::::::::
     * ::
     * :: PUBLIC FUNCTIONS
     * ::
     * ::
     * :: GIFGetFrames ( )
     * ::
     */
    public function GIFGetFrames() {
        return ($this->GIF_arrays);
    }

    /*
     * :::::::::::::::::::::::::::::::::::::::::::::::::::
     * ::
     * :: GIFGetDelays ( )
     * ::
     */
    public function GIFGetDelays() {
        return ($this->GIF_delays);
    }

}