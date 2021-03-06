<?php 
/**
 * fichier de définition et construction de l'objet Systeme
 */
namespace BigBang;

/**
 * classe de définition et construction de l'objet Systeme
 */
class Systeme extends Object{
	
	/***
	 * identifier
	 * @var integer
	 */
	public $id;
	
	/***
	 * nom textuel
	 * @var string
	 */
	public $name;
		
	/***
	 * coordonnée angulaire (ou angle dans le système de coordonées polaires) en degrées
	 * @var float
	 */
	public $angle;
	
	/***
	 *  coordonnée radiale (ou rayon dans le système de coordonées polaires) en années-lumières
	 * @var float
	 */
	public $distance;
	
	/***
	 * altitude par rapport au 0 absolu de la galaxie en années-lumières
	 * pour des raisons de lecture on a choisi une altitude plutot qu'un angle
	 * @var float
	 */
	public $altitude;
	
	/**
	 * constructeur de classe
	 * @param string $name nom optionnel du systeme
	 */
	public function __construct($name=null){
		if($name==null){
			$name=$this->RandomString(10);	
		}
		
		$this->name=$name;
		$this->create_coordinates();
	}
	
	/**
	 * surcharge pour mettre le texte
	 * @see \BigBang\Object::__toString()
	 */
	public function __toString(){
		return "Système ".$this->name.", coordonées ".$this->angle." ".$this->distance." ".$this->altitude."";
	}
	
	/***
	 * (non-PHPdoc)
	 * @see \BigBang\Object::__toSqlValues()
	 */
	public function __toSqlValues(){
		return "('','".$this->name."','".$this->angle."','".$this->distance."','".$this->altitude."')";
	}
	
	/**
	 * détermine des coordonées pseudo-aléatoire qui correspondent au modèle de galaxie demandé
	 */
	private function create_coordinates(){
		//déterminer la zone (bras ou corps)
		$probaZone=rand(0,100);
		$zone="partout";
		foreach (Galaxy::$probaPosition as $key=>$potentiel){
			if($probaZone<=$key){$zone=$potentiel;break;}
		}
		
		$this->altitude=maths_service::probaDescendante(0,maths_service::float_rand(0,bcdiv(Galaxy::$altitudeMax,2),2),4);
		if(rand(0,10)<=5){
			$this->altitude=-$this->altitude;
		}
		
		switch ($zone){
			case "bras":
				//quel bras ?
				$brasIndex=rand(0,count(Galaxy::$bras)-1);
				//$this->angle=maths_service::float_rand(Galaxy::$angleMin,Galaxy::$bras[$brasIndex]['ouverture'],4);
				$this->angle=maths_service::probaDescendante(Galaxy::$angleMin,Galaxy::$bras[$brasIndex]['ouverture'],4);

				//calcul de la distance moyenne pour être dans un bras à l'angle donné
				//en considérant qu'un bras atteind la bordure en un tour complet (à vérifier)
				$distanceMoyenne=bcmul(Galaxy::$rayonMax, bcdiv($this->angle,Galaxy::$bras[$brasIndex]['ouverture'],4),4);
				$distanceMin=$distanceMoyenne-(Galaxy::$epaisseurBras/2);
				$distanceFinale=$distanceMoyenne+maths_service::float_rand(0,Galaxy::$epaisseurBras,2);
				
				//$this->name="bras";
				
				//$this->distance=$distanceFinale;
				$this->distance=$distanceFinale;
				$this->angle+=Galaxy::$bras[$brasIndex]['angle'];
			
				break;
			
			//le bulbe contient 5 à 10% des étoiles
			case "bulbe":				
				$this->angle=maths_service::float_rand(0,360,4);
				
				//on inverse la proba descendante pour tenir compte de la répartition en boule
				$distanceBrute=bcsub(Galaxy::$rayonMin,maths_service::probaDescendante(1,Galaxy::$rayonMin,4));
				
				//version 1: théorème de pythagore AB²=AC²+BC²
				$ab2=bcmul($distanceBrute,$distanceBrute);
				$bc=maths_service::float_rand(0, $distanceBrute,4);
				$bc2=bcmul($bc,$bc);
				$ac2=$ab2-$bc2;
				$ac=sqrt($ac2);
				
				
				$this->distance=$ac;
				$this->altitude=$bc;
				
				/**
				// version 2 : loi des sinus: a / sin(α) = b / sin(β) = c / sin(γ) 
				$C=$angleVertical=maths_service::probaDescendante(0,90,4);
				$a=$distanceBrute;
				$A=90;
				$B=180-$C-$A;
				$b=$a*sin(deg2rad($B));
				$c=$a*sin(deg2rad($C));
				
				$this->distance=$c;
				$this->altitude=$b;
				*/
				//$this->altitude=maths_service::probaDescendante(0,maths_service::float_rand(0,bcdiv(Galaxy::$epaisseurBulbe,2),2),4);
				if(rand(0,10)<=5){
					$this->altitude=-$this->altitude;
				}
				break;
			case "partout":
			default:
				
				$this->angle=maths_service::float_rand(0,360,4);
				//$this->distance=maths_service::float_rand(Galaxy::$rayonMin,Galaxy::$rayonMax,2);
				$this->distance=maths_service::probaDescendante(Galaxy::$rayonMin,Galaxy::$rayonMax,2);
				break;	
		}
		//correctif pour les bras notamment
		if($this->angle>360){
			$tmpangle=bcdiv($this->angle,360,8);
			$this->angle=bcmul(bcsub( $tmpangle, floor($tmpangle)) , 360)+360;
			
		}
		
	}
	
	/**
	 * renvoie une chaine de caractères aléatoires de taille spécifiée
	 * @param integer $length
	 */
	private function RandomString($length) {
	    $keys = array_merge(range('a', 'z'), range('A', 'Z'));
	    $key="";
	    for($i=0; $i < $length; $i++) {
	        $key .= $keys[array_rand($keys)];
	    }
	    return $key;
	}
	


}

?>