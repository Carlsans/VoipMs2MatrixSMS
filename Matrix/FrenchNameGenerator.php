<?php
include_once 'ServerIdentity.php';

class FrenchNameGenerator {

    private $firstNamePrefixes = [
        'Al', 'An', 'Be', 'Ch', 'Da', 'De', 'Em', 'Fr', 'Ga', 'Gu',
        'He', 'Is', 'Je', 'Jo', 'La', 'Le', 'Lo', 'Lu', 'Ma', 'Mi',
        'Ni', 'Ol', 'Pa', 'Pi', 'Ra', 'Re', 'Ro', 'Se', 'Si', 'Th',
        'Vi', 'Ya', 'Yv', 'Za', 'Ph', 'Ge', 'Fe', 'Ed', 'Di', 'Cl'
    ];
    
    private $firstNameSuffixes = [
        'ain', 'and', 'ard', 'as', 'eau', 'enc', 'ent', 'ert', 'est', 'ien',
        'ier', 'on', 'ois', 'ric', 'tre', 'ard', 'elle', 'ette', 'iane', 'ie',
        'ine', 'onne', 'tte', 'ile', 'ise', 'anne', 'ene', 'iel', 'ien', 'in'
    ];
    
    // French-sounding fantasy last name components
    private $lastNamePrefixes = [
        'Bel', 'Cha', 'Des', 'Du', 'Fon', 'Gri', 'La', 'Le', 'Lun', 'Mar',
        'Mon', 'Neu', 'Poi', 'Rai', 'Ren', 'Ros', 'Sai', 'Sol', 'Val', 'Ven',
        'Vil', 'Voi', 'Bou', 'Car', 'Dor', 'Fau', 'Gar', 'Lan', 'Mer', 'Noc'
    ];
    
    private $lastNameSuffixes = [
        'ard', 'aux', 'bert', 'bois', 'chet', 'dette', 'doux', 'fort', 'gard', 'geux',
        'lier', 'loire', 'maire', 'mont', 'nard', 'neux', 'nier', 'noire', 'quet', 'rand',
        'rien', 'sard', 'tain', 'tel', 'tier', 'vain', 'vert', 'veux', 'vier', 'zard'
    ];

    private $lastNameInfixes = [
        'de', 'du', 'de la', 'des', 'le', 'la', 'les', 'au', 'aux', 'en',
        'sur', 'sous', 'par', 'dans', 'vers', 'près', 'entre', 'avec', 'sans', 'pour'
    ];

    private $secretKey;

    public function __construct() {
        $serverid = new ServerIdentity();
        $this->secretKey = $serverid->secretnamegenerator;
    }

    /**
     * Generate deterministic indices from a phone number
     */
    private function getIndices(string $phoneNumber): array {
        $hash = hash_hmac('sha256', $phoneNumber, $this->secretKey);
        
        return [
            hexdec(substr($hash, 0, 4)) % count($this->firstNamePrefixes),
            hexdec(substr($hash, 4, 4)) % count($this->firstNameSuffixes),
            hexdec(substr($hash, 8, 4)) % count($this->lastNamePrefixes),
            hexdec(substr($hash, 12, 4)) % count($this->lastNameSuffixes),
            hexdec(substr($hash, 16, 4)) % count($this->lastNameInfixes),
            hexdec(substr($hash, 20, 4)) % 3  // Complexity factor for last name
        ];
    }

    /**
     * Generate a unique French fantasy name from a 10-digit phone number
     */
    public function generateName(string $phoneNumber): string {
        // Strip any non-numeric characters
        $number = preg_replace('/[^0-9]/', '', $phoneNumber);
        
        if (strlen($number) !== 10) {
            throw new InvalidArgumentException('Phone number must be exactly 10 digits');
        }

        // Get deterministic indices
        [$firstPrefixIdx, $firstSuffixIdx, $lastPrefixIdx, $lastSuffixIdx, $infixIdx, $complexity] = 
            $this->getIndices($number);

        // Generate first name
        $firstName = $this->firstNamePrefixes[$firstPrefixIdx] . 
                    strtolower($this->firstNameSuffixes[$firstSuffixIdx]);

        // Generate last name based on complexity
        switch ($complexity) {
            case 0:
                // Simple: prefix + suffix
                $lastName = $this->lastNamePrefixes[$lastPrefixIdx] . 
                          strtolower($this->lastNameSuffixes[$lastSuffixIdx]);
                break;
                
            case 1:
                // Compound: prefix + suffix + suffix
                $secondSuffixIdx = ($lastSuffixIdx + 7) % count($this->lastNameSuffixes);
                $lastName = $this->lastNamePrefixes[$lastPrefixIdx] . 
                          strtolower($this->lastNameSuffixes[$lastSuffixIdx]) .
                          strtolower($this->lastNameSuffixes[$secondSuffixIdx]);
                break;
                
            case 2:
                // Noble: prefix + infix + prefix + suffix
                $secondPrefixIdx = ($lastPrefixIdx + 13) % count($this->lastNamePrefixes);
                $lastName = $this->lastNamePrefixes[$lastPrefixIdx] . 
                          ' ' . $this->lastNameInfixes[$infixIdx] . ' ' .
                          strtolower($this->lastNamePrefixes[$secondPrefixIdx]) .
                          strtolower($this->lastNameSuffixes[$lastSuffixIdx]);
                break;
        }

        return $firstName . ' ' . $lastName;
    }
}





?>