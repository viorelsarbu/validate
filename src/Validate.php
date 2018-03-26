<?php 
namespace Viorel\Validation;

class Validate
{
    public function __construct()
    {

    }

    public function isNumber( $str ) {
        $patern = "/^[0-9]+$/u";
        return ( preg_match( $patern, $str ) != 0 );
    }


    /**
     * @param string $str
     * @return bool
     */
    public static function isNumberStatic(string $str) : bool
    {
        $patern = "/^[0-9]+$/u";
        if(preg_match( $patern, $str ) != 0){
            return true;
        }
        return false;
    }

    public function isAlfanumeric( $sir ) {
        $patern = "/^[a-zA-Z0-9]+$/u";

        return ( preg_match( $patern, $sir ) != 0 );
    }

    public function isName( $str ) {
        $patern = '/^[a-z\- àâçéèêëîïôûùüÿñæœ]+$/i';

        return ( preg_match( $patern, $str ) != 0 );
    }

    public function isIBAN( $str ) {
        // SS00 0000 0000 00AA AAAA AAAA A00
        $str = str_replace( ' ', '', $str );

        $this->setErrorMessage( 'Ce code IBAN n’est pas valide' );

        if ( strlen( $str ) != 27 ) {
            return false;
        }

        if ( substr( $str, 0, 2 ) != 'FR' ) {
            return false;
        }

        if ( ! $this->Number( substr( $str, 2, 12 ) ) ) {
            return false;
        }

        if ( ! $this->Alfanumeric( substr( $str, 14, 11 ) ) ) {
            return false;
        }

        if ( ! $this->Number( substr( $str, 25, 2 ) ) ) {
            return false;
        }

        return true;
        //return Iban::validate($str);
    }

    public function BIC( $str ) {
        $str = str_replace( ' ', '', $str );

        $this->setErrorMessage( 'Ce code BIC n’est pas valide.' );

        if ( substr( $str, 4, 2 ) != 'FR' ) {
            return false;
        }

        // TODO: de instalat SwiftBic
        return SwiftBic::validate( $str );
    }


    public function UniqueCode( $str ) {
        $this->load->model( 'codes/Codes_model', 'mdl_codes' );
        $status = $this->mdl_codes->GetStatus( $str );

        if ( $status == Codes_model::CODE_INVALID ) {
            $this->setErrorMessage( 'Le code est incorrect.' );

            return false;
        } elseif ( $status == Codes_model::CODE_USED ) {
            $this->setErrorMessage( 'Votre code a déjà été utilisé.' );

            return false;
        }

        return true;
    }

    public function GeneralCheckboxValid( $str ) {
        return ( $str == null || in_array( $str, array( '0', '1' ) ) );
    }

    public function GeneralCheckboxIsChecked( $str ) {
        return ( $str == 1 );
    }

    public function GeneralDate( $str ) {
        if ( sizeof( explode( "/", $str ) ) != 3 ) {
            return false;
        }

        if ( strlen( $str ) > 0 && $str != '//' ) {
            list( $day, $month, $year ) = explode( "/", $str );

            if ( ! is_numeric( $day ) || ! is_numeric( $month ) || ! is_numeric( $year ) ) {
                return false;
            }

            if ( ! checkdate( $month, $day, $year ) ) {
                return false;
            }
        }

        $d = DateTime::createFromFormat( 'd/m/Y', $str );
        if ( ! $d ) {
            return false;
        }

        return true;
    }

    public function GeneralDateInThePast( $str ) {
        $this->load->module( 'timeframe' );

        if ( sizeof( explode( "/", $str ) ) != 3 ) {
            return false;
        }

        if ( strlen( $str ) > 0 && $str != '//' ) {
            list( $day, $month, $year ) = explode( "/", $str );

            if ( ! is_numeric( $day ) || ! is_numeric( $month ) || ! is_numeric( $year ) ) {
                return false;
            }

            if ( ! checkdate( $month, $day, $year ) ) {
                return false;
            }
        }

        $d = DateTime::createFromFormat( 'd/m/Y', $str );
        if ( ! $d ) {
            return false;
        }

        if ( $d > $this->mdl_timeframe->config( 'now' ) ) {
            return false;
        }

        return true;
    }

    public function ValidNom( $str ) {
        if ( $str == '' ) {
            return true;
        }

        $this->setErrorMessage( 'Le champ {field} ne peut contenir que des lettres.' );

        return $this->Name( $str );
    }

    public function ValidPrenom( $str ) {
        if ( $str == '' ) {
            return true;
        }

        $this->setErrorMessage( 'Le champ {field} ne peut contenir que des lettres.' );

        return $this->Name( $str );
    }

    public function ValidVille( $str ) {
        if ( $str == '' ) {
            return true;
        }

        $this->setErrorMessage( 'Le champ %s ne peut contenir que des lettres.' );

        return ( preg_match( '/^[a-z áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœÁÀÂÄÃÅÇÉÈÊËÍÌÎÏÑÓÒÔÖÕÚÙÛÜÝŸÆŒ\/\'-]+$/i',
                $str ) != 0 );
    }

    public function ValidEmailDomain( $str ) {
        if ( $str == '' ) {
            return true;
        }

        $checker = new EmailChecker\EmailChecker();

        if ( ! $checker->isValid( $str ) ) {
            $this->setErrorMessage( 'Cette adresse e-mail n’est pas valide.' );

            return false;
        }

        return true;
    }

    public function ValidEmail( $str ) {
        $this->setErrorMessage( 'Votre adresse e-mail n\'est pas valide, merci de la corriger.' );

        return ( ! preg_match( "/^([a-z0-9\+_\-]+)(\.[a-z0-9\+_\-]+)*@([a-z0-9\-]+\.)+[a-z]{2,6}$/ix",
            $str ) ) ? false : true;
    }

    public function ValidAddress( $str, $with_number = false ) {
        if ( $str == "" ) {
            return true;
        }

        $this->setErrorMessage( 'Veuillez indiquer une %s valide.' );

        if ( preg_match( '/^[a-z 0-9áàâäãåçéèêëíìîïñóòôöõúùûüýÿæœÁÀÂÄÃÅÇÉÈÊËÍÌÎÏÑÓÒÔÖÕÚÙÛÜÝŸÆŒ,;\.\/\'-]+$/i',
                $str ) == 0 ) {
            return false;
        } else {
            $str = str_replace( array( '-', '.', ',', ' ' ), '', $str );

            if ( $this->Number( $str ) ) {
                return false;
            } elseif ( $with_number && preg_match( '/^[a-zA-Z]+$/u', $str ) != 0 ) {
                return false;
            }
        }

        return true;
    }

    public function ValidAddressWithNumber( $str ) {
        return $this->ValidAddress( $str, true );
    }

    public function ValidCivilite( $str ) {
        if ( $str == '' ) {
            return true;
        }

        $this->setErrorMessage( 'Veuillez indiquer votre %s.' );

        return in_array( $str, array( 'Mme', 'M', 'Mlle' ) );
    }

    public function ValidBirthDay( $str ) {
        $this->setErrorMessage( 'Veuillez indiquer votre %s.' );

        return in_array( $str, range( 1, 31 ) );
    }

    public function ValidBirthMonth( $str ) {
        $this->setErrorMessage( 'Veuillez indiquer votre %s.' );

        return in_array( $str, range( 1, 12 ) );
    }

    public function ValidBirthYear( $str ) {
        $this->setErrorMessage( 'Veuillez indiquer votre %s.' );

        $min_age = $this->config( 'date_min_age_required' );

        return in_array( $str, range( 1920, ( date( 'Y' ) - ( $min_age ? $min_age : 0 ) ) ) );
    }

    public function ValidBirthDate( $not_used ) {
        $d = $this->input->post( $this->config( 'date_day_field' ) );
        $m = $this->input->post( $this->config( 'date_month_field' ) );
        $y = $this->input->post( $this->config( 'date_year_field' ) );

        if ( ! $this->ValidBirthDay( $d ) || ! $this->ValidBirthMonth( $m ) || ! $this->ValidBirthYear( $y ) ) {
            $this->setErrorMessage( 'Veuillez indiquer votre date de naissance.' );
        }

        $BirthDateValidate = $this->CalculateAge( $d . '/' . $m . '/' . $y, $this->config( 'date_min_age_required' ) );

        switch ( $BirthDateValidate ) {
            case 0: // invalid birthdate, or didn't select birthdate
                $BirthDateErrorMessage = 'Veuillez indiquer votre date de naissance.';
                break;

            case 1: // valid birthdate
                $BirthDateErrorMessage = '';
                break;

            case 2: // under 18 year old
                $BirthDateErrorMessage = 'Vous devez avoir 18 ans pour participer à l’offre.';
                break;

            case 3: // birth date selection invalid (for ex. 30 feb)
                $BirthDateErrorMessage = 'Vous avez une erreur dans le date de naissance selection!';
                break;
        }

        $this->setErrorMessage( $BirthDateErrorMessage );

        return ( $BirthDateErrorMessage == '' );
    }

    public function ValidBirthDateOneField( $value ) {
        if ( ! $value ) {
            $this->setErrorMessage( 'Veuillez indiquer votre date de naissance.' );
        }

        $BirthDateValidate = $this->CalculateAge( $value, $this->config( 'date_min_age_required' ) );

        switch ( $BirthDateValidate ) {
            case 0: // invalid birthdate, or didn't select birthdate
                $BirthDateErrorMessage = 'Veuillez indiquer votre date de naissance.';
                break;

            case 1: // valid birthdate
                $BirthDateErrorMessage = '';
                break;

            case 2: // under 18 year old
                $BirthDateErrorMessage = 'Vous devez avoir 18 ans pour participer à l’offre.';
                break;

            case 3: // birth date selection invalid (for ex. 30 feb)
                $BirthDateErrorMessage = 'Vous avez une erreur dans le date de naissance selection!';
                break;
        }

        $this->setErrorMessage( $BirthDateErrorMessage );

        return ( $BirthDateErrorMessage == '' );
    }


    public function ValidBirthDateOptionalYear( $not_used ) {
        $d = $this->input->post( $this->config( 'date_day_field' ) );
        $m = $this->input->post( $this->config( 'date_month_field' ) );
        $y = $this->input->post( $this->config( 'date_year_field' ) );

        if ( $y != '' ) {
            return $this->ValidBirthDate( $not_used );
        }

        $valid_days = array(
            1  => 31,
            2  => 29,
            3  => 31,
            4  => 30,
            5  => 31,
            6  => 30,
            7  => 31,
            8  => 31,
            9  => 30,
            10 => 31,
            11 => 30,
            12 => 31,
        );

        if ( ! $this->ValidBirthDay( $d ) || ! $this->ValidBirthMonth( $m ) ) {
            $this->setErrorMessage( 'Veuillez indiquer votre date de naissance.' );

            return false;
        }
        if ( $d > $valid_days[ $m ] ) {
            $this->setErrorMessage( 'Vous avez une erreur dans le date de naissance selection!' );

            return false;
        }

        return true;
    }

    public function ValidRecaptcha( $str ) {
        $this->load->module( 'recaptcha' );

        if ( $str == '' ) {
            $this->setErrorMessage( 'Veuillez cliquer sur la phrase "Je ne suis pas un robot"' );

            return false;
        }
        $context = stream_context_create( array(
            'http' => array(
                'header'  => 'Content-type: application/x-www-form-urlencoded\r\n',
                'method'  => 'POST',
                'content' => http_build_query( array(
                    'secret'   => $this->mdl_recaptcha->config( 'GoogleSecretKey' ),
                    'response' => $str,
                    'remoteip' => $_SERVER['REMOTE_ADDR']
                ) )
            )
        ) );
        $result  = file_get_contents( 'https://www.google.com/recaptcha/api/siteverify', false, $context );
        $json    = json_decode( $result, true );
        if ( ! $json['success'] ) {
            $this->setErrorMessage( 'Veuillez valider le test reCAPTCHA.' );

            return false;
        }

        return true;
    }

    /* HELPERS */
    private function setErrorMessage( $msg ) {
        $method = debug_backtrace( DEBUG_BACKTRACE_PROVIDE_OBJECT, 2 )[1]['function'];
        $this->form_validation->set_message( $method . '_callable', $msg );
    }

    // 0 - invalid birthdate, or didn't select birthdate
    // 1 - valid birthdate
    // 2 - under 18 year old
    // 3 - birth date selection invalid (for ex. 30 feb)
    public function CalculateAge( $birthday, $min_age = 18 ) {
        if ( sizeof( explode( "/", $birthday ) ) != 3 ) {
            return 0;
        }

        if ( strlen( $birthday ) > 0 && $birthday != '//' ) {
            list( $day, $month, $year ) = explode( "/", $birthday );

            if ( ! is_numeric( $day ) || ! is_numeric( $month ) || ! is_numeric( $year ) ) {
                return 0;
            }

            if ( checkdate( $month, $day, $year ) ) {
                $year_diff  = date( "Y" ) - $year;
                $month_diff = date( "m" ) - $month;
                $day_diff   = date( "d" ) - $day;
                if ( $month_diff < 0 || ( $day_diff < 0 && $month_diff == 0 ) ) {
                    $year_diff --;
                }
                if ( $min_age !== false && $year_diff < $min_age ) {
                    $returnVal = 2;
                } else {
                    $returnVal = 1;
                }
            } else {
                $returnVal = 3;
            }
        } else {
            $returnVal = 0;
        }

        return $returnVal;
    }
}