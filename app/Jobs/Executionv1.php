<?php

namespace App\Jobs;

use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class Executionv1
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    protected $registers = array(16);
    protected $ram = array(16000);
    protected $pc = 0;
    public function loader($code)
    {
        $this->ram = explode("\\n", $code);
    }

    protected function unload()
    {
        $str = implode("\\n", $this->ram);
        return $str;
    }

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($code)
    {
        $this->loader($code);
    }

    /**
     * Execute the job.
     * IF/ID/OF & control unit
     * @return void
     */
    public function handle($code)
    {
        $halt = true;
        while ($halt) {
            $line = $this->ram[$this->pc];
            $mem = false;
            $wb = false;
            $branch = false;
            $immediate = false;
            $pcval = 1;
            $pcmul = false;
            $op = substr($line, 4, 8);
            $rs = substr($line, 8, 12);
            $rt = substr($line, 12, 16);
            $rd = substr($line, 16, 20);
            $imm = substr($line, 16, 32);
            // TODO : ending project part here
            switch ($op) {
                case "0000":{
                        $wb = true;
                        $res = $this->execute('+', $this->registers[bindec($rs)],
                         $this->registers[bindec($this->alusrcmultiplexer("0000",$rt , $imm))]);
                        $this->writeback($wb,
                        $this->wbsrcmultiplexer("0000" , null , $this->destimultiplexer("0000" , $rt , $rd)));
                    }break;
                case "0001":{
                    $wb = true;
                    $res = $this->execute('-', $this->registers[bindec($rs)],
                     $this->registers[bindec($this->alusrcmultiplexer("0001",$rt , $imm))]);
                    $this->writeback($wb,
                    $this->wbsrcmultiplexer("0001" , null , $this->destimultiplexer("0001" , $rt , $rd)));
                    }break;
                case "0010":{
                    $wb = true;
                    $res = $this->execute('<', $this->registers[bindec($rs)],
                     $this->registers[bindec($this->alusrcmultiplexer("0010",$rt , $imm))]);
                    $this->writeback($wb,
                    $this->wbsrcmultiplexer("0010" , null , $this->destimultiplexer("0010" , $rt , $rd)));
                }break;
                case "0011":{
                    $wb = true;
                    $res = $this->execute('|', $this->registers[bindec($rs)],
                     $this->registers[bindec($this->alusrcmultiplexer("0011",$rt , $imm))]);
                    $this->writeback($wb,
                    $this->wbsrcmultiplexer("0011" , null , $this->destimultiplexer("0011" , $rt , $rd)));
                    }break;
                case "0100":{
                    $wb = true;
                    $res = $this->execute('!&', $this->registers[bindec($rs)],
                     $this->registers[bindec($this->alusrcmultiplexer("0100",$rt , $imm))]);
                    $this->writeback($wb,
                    $this->wbsrcmultiplexer("0100" , null , $this->destimultiplexer("0100" , $rt , $rd)));
                    }break;
                case "0101":{
                    $wb = true;
                    $res = $this->execute('+', $this->registers[bindec($rs)],
                     $this->registers[bindec($this->alusrcmultiplexer("0101",$rt , $imm))]);
                    $this->writeback($wb,
                    $this->wbsrcmultiplexer("0101" , null , $this->destimultiplexer("0101" , $rt , $rd)));
                    }break;
                case "0110":{
                    $wb = true;
                    $res = $this->execute('<', $this->registers[bindec($rs)],
                     $this->registers[bindec($this->alusrcmultiplexer("0110",$rt , $imm))]);
                    $this->writeback($wb,
                    $this->wbsrcmultiplexer("0110" , null , $this->destimultiplexer("0110" , $rt , $rd)));
                    }break;
                case "0111":{
                    $wb = true;
                    $res = $this->execute('|', $this->registers[bindec($rs)],
                     $this->registers[bindec($this->alusrcmultiplexer("0111",$rt , $imm))]);
                    $this->writeback($wb,
                    $this->wbsrcmultiplexer("0111" , null , $this->destimultiplexer("0111" , $rt , $rd)));
                    }break;
                case "1000":{
                    $wb = true;
                    $res = $this->execute('^', $this->registers[bindec($rs)],
                     $this->registers[bindec($this->alusrcmultiplexer("1000",$rt , $imm))]);
                    $this->writeback($wb,
                    $this->wbsrcmultiplexer("1000" , null , $this->destimultiplexer("1000" , $rt , $rd)));
                    }break;
                case "1001":{
                        // lw mem and op both
                        $mem = true;
                        $res = $this->execute('|', $this->ram[bindec($rs)], $this->ram[bindec($rt)]);
                        $this->registers[$rt] = $this->wbsrcmultiplexer("1001" , $this->memory($mem, '1001', $res, null) , null);
                    }break;
                case "1010":{
                        // sw mem and op both
                        $mem = true;
                        $res = $this->execute('+', $this->ram[bindec($rs)], $this->ram[bindec($rt)]);
                        $this->memory($mem , "1010" , $res , $val);
                    }break;
                case "1011":{
                        // beq
                        $branch = true;
                        $pcval = $this->execute('==', $this->ram[bindec($rs)], $this->ram[bindec($rt)]);
                        $pcmul = 1;
                    }break;
                case "1100":{
                        // jalr set rt jump rs
                        $this->registers[$this->destimultiplexer("1100" , $rt , $rd)] = $this->pc + 1;
                        $this->pc = $registers[$rs];
                        continue;
                    }break;
                case "1101":{
                        // j to imm
                        $this->pc = $imm;
                        continue;
                    }break;
                case "1110":{
                        $halt = false;
                    }break;
            }
            // in the beq set the pcmul and a pcval
            $this->pcAdd($pcmul, $pcval);
        }

        $this->unload();
    }

    /**
     * 0 : beq
     * 1 : usual
     * 2 : setval jalr
     */
    public function pcAdd($pcmul, $val)
    {
        switch ($pcmul) {
            case 0:{
                    $this->pc += $val;
                }break;
            case 1:{
                    $this->pc += 1;
                }break;
            case 2:{
                    $this->pc = $val;
                }break;
        }
    }

    // returning array of boolean boolean
    public function destimultiplexer($op, $rt, $rd)
    {
        $answer = [];
        switch ($op) {
            case "0000":{
                    return $rd;
                }break;
            case "0001":{
                    return $rd;
                }break;
            case "0010":{
                    return $rd;
                }break;
            case "0011":{
                    return $rd;
                }break;
            case "0100":{
                    return $rd;
                }break;
            case "0101":{
                    return $rt;
                }break;
            case "0110":{
                    return $rt;
                }break;
            case "0111":{
                    return $rt;
                }break;
            case "1000":{
                    return $rt;
                }break;
            case "1001":{
                    return $rt;
                }break;
            case "1010":{
                    return null;
                }break;
            case "1011":{
                    return null;
                }break;
            case "1100":{
                    return $rt;
                }break;
            case "1101":{
                    return null;
                }break;
            case "1110":{
                    return null;
                }break;
            case "1111":{
                    return nul;
                }break;
        }
    }

    public function alusrcmultiplexer($operation, $rt, $imm)
    {

    }

    // bindec both negative and positive numbers
    public function bindec32($bin)
    {
        if (strlen($bin) == 32 && $bin[0] == '1') {
            for ($i = 0; $i < 32; $i++) {
                $bin[$i] = $bin[$i] == '1' ? '0' : '1';
            }

            return (bindec($bin) + 1) * -1;
        }
        return bindec($bin);
    }

    // decimal to binary signed form
    protected function extention($signed, $decimal)
    {
        // making the signed binary value
        switch ($signed) {
            case 0:{
                    if ($decimal < 0) {
                        $imm = decbin($decimal);
                    } else {
                        $imm = decbin($decimal);
                        $temp = "00000000000000000000000000000000";
                        $j = 31;
                        for ($i = strlen($imm) - 1; $i >= 0; $i--) {
                            $temp[$j] = $imm[$i];
                            $j--;
                        }
                        $imm = $temp;
                    }
                }break;

            case 1:{
                    $imm = "00000000000000000000000000000000";
                    $answer = "" . decbin($decimal);
                    $i = 31;
                    for ($j = strlen($imm) - 1; $j >= 0; $j--) {
                        $imm[$i] = $answer[$j];
                        $i--;
                    }
                }break;
        }

        return $imm;
    }

    // EXE
    public function execute($operation, $val1, $val2)
    {
        $answer = 0;
        // TODO : do stuff here and execution part
        switch ($operation) {
            case '+':{
                    $answer = $val1 + $val2;
                }break;
            case '-':{
                    $answer = $val1 - $val2;
                }break;
            case '&':{
                    $val1 = $this->extention(1, $val1);
                    $val2 = $this->extention(1, $val2);
                    $answer = $this->bindec32($val1 & $val2);
                }break;
            case '!&':{
                    $val1 = $this->extention(1, $val1);
                    $val2 = $this->extention(1, $val2);
                    $answer = $this->bindec32(!($val1 & $val2));
                }break;
            case '|':{
                    $val1 = $this->extention(1, $val1);
                    $val2 = $this->extention(1, $val2);
                    $answer = $this->bindec32($val1 | $val2);
                }break;
            case '==':{
                    $answer = $val1 - $val2;
                }break;
            case '<':{
                    if ($val1 < $val2) {
                        $answer = 1;
                    } else {
                        $answer = 0;
                    }
                }break;
                case '^' : {
                    $answer = decbin($val1) << 16;
                }
        }
        return $answer;
    }

    // MEM using mul here
    public function memory($mem, $op, $dest, $val)
    {
        $res = $this->multiplexer($op);
        if ($mem) {
            if ($op == '1010') {
                $this->ram[$dest] = $val;
            } else {
                return $this->ram[$dest];
            }
        }
    }

    // WB using mul here
    public function writeback($wb, $dest, $val)
    {
        $res = $this->multiplexer($op);
        if ($wb) {
            $this->registers[$dest] = $val;
        }
    }

    public function wbsrcmultiplexer($op , $mem , $wbres)
    {
        if($op == "1001"){
            return $mem;
        }else{
            return $wbres;
        }
    }
}
