<?php

class IICA_Entities extends PDO {
/**
* Cette classe gère les entités.
*
* PHP version 5
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @version 1.0
*/

	public function __construct( $_Host, $_Port, $_Driver, $_Base, $_User, $_Password ) {
	/**
	* Connexion à la base de données.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-07
	*
	* @param[in] $_Host Noeud sur lequel s'exécute la base de données
	* @param[in] $_Port Port IP sur lequel répond la base de données
	* @param[in] $_Driver Type de la base de données
	* @param[in] $_Base Nom de la base de données
	* @param[in] $_User Nom de l'utilisateur dans la base de données
	* @param[in] $_Password Mot de passe de l'utilisateur dans la base de données
	*
	* @return Renvoi un booléen sur le succès de la connexion à la base de données
	*/
		$DSN = $_Driver . ':host=' . $_Host . ';port=' . $_Port . ';dbname=' . $_Base ;
		
		parent::__construct( $DSN, $_User, $_Password );
		
		return true;
	}


	/* ===============================================================================
	** Gestion des Entités
	*/
	
	public function set( $ent_id, $Code, $Label ) {
	/**
	* Créé ou actualise une Entité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-13
	*
	* @param[in] $ent_id Identifiant de l'entité à modifier (si précisé)
	* @param[in] $Code Code de l'entité
	* @param[in] $Label Libeller de l'entité
	*
	* @return Renvoi vrai si l'entité a été créée ou modifiée.
	*/
		if ( $ent_id == '' ) {
			if ( ! $Result = $this->prepare( 'INSERT INTO ent_entities ' .
				'( ent_code, ent_label ) ' .
				'VALUES ( :Code, :Label )' ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		} else {
			if ( ! $Result = $this->prepare( 'UPDATE ent_entities SET ' .
				'ent_code = :Code, ent_label = :Label ' .
				'WHERE ent_id = :ent_id' ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
			
			if ( ! $Result->bindParam( ':ent_id', $ent_id, PDO::PARAM_INT ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}
				
      $Code = strtoupper( $Code );
		if ( ! $Result->bindParam( ':Code', $Code, PDO::PARAM_STR, 6 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
				
		if ( ! $Result->bindParam( ':Label', $Label, PDO::PARAM_STR, 35 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		return true;
	}


	public function listEntities( $Type = 0, $orderBy = 'code' ) {
	/**
	* Lister les Entités.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-13
	*
	* @param[in] $Type Permet d'afficher ou pas les Entités supprimées logiquement.
	* @param[in] $orderBy Permet de gérer l'ordre d'affichage.
	*
	* @return Renvoi une liste d'Entités ou une liste vide.
	*/
		$Request = 'SELECT ' .
		 'ent_id, ent_code, ent_label ' .
		 'FROM ent_entities ' ;

		if ( $Type == 0 ) {
			$Request .= 'WHERE ent_logical_delete = false ' ;
		}
		
		switch( $orderBy ) {
		 default:
		 case 'code':
		 	$Request .= 'ORDER BY ent_code ';
			break;

		 case 'code-desc':
		 	$Request .= 'ORDER BY ent_code DESC ';
			break;

		 case 'label':
		 	$Request .= 'ORDER BY ent_label ';
			break;

		 case 'label-desc':
		 	$Request .= 'ORDER BY ent_label DESC ';
			break;
		}
		 
		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Data = array();
		
		while ( $Occurrence = $Result->fetchObject() ) {
			$Data[] = $Occurrence;
		}
 
 		return $Data;
	}


	public function get( $ent_id, $Type = 0 ) {
	/**
	* Récupère les informations d'une Entité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-13
	*
	* @param[in] $ent_id Identifiant de l'entité à récupérer
	* @param[in] $Type Permet d'afficher ou pas une Entité supprimée logiquement.
	*
	* @return Renvoi l'occurrence d'une Entité
	*/
		$Data = false;
		
		$Request = 'SELECT ' .
		 'ent_id, ent_code, ent_label ' .
		 'FROM ent_entities ' .
		 'WHERE ent_id = :ent_id ' ;

		if ( $Type == 0 ) {
			$Request .= 'AND ent_logical_delete = false' ;
		}
		 
		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->bindParam( ':ent_id', $ent_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		return $Result->fetchObject() ;
	}


	public function delete( $ent_id, $Type = 0 ) {
	/**
	* Supprimer une Entité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-13
	*
	* @param[in] $ent_id Identifiant de l'entité à supprimer
	* @param[in] $Type Permet de supprimer une Entité logiquement ou physiquement.
	*
	* @return Renvoi vrai si l'Entité a été supprimée
	*/
      include( DIR_LIBRARIES . '/Config_Access_Tables.inc.php' );
      
		/*
		** Démarre la transaction.
		*/
		$this->beginTransaction();
		
		
		/*
		** Détruit l'entité (logiquement ou physiquement.
		*/
		if ( $Type == 0 ) {  // Suppression logique
			if ( ! $Result = $this->prepare( 'UPDATE ' .
			 'ent_entities ' .
			 'SET ent_logical_delete = true ' .
			 'WHERE ent_id = :ent_id' ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		} else {
			if ( ! $Result = $this->prepare( 'DELETE ' .
			 'FROM ent_entities ' .
			 'WHERE ent_id = :ent_id' ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}
		
		$Result->bindParam( ':ent_id', $ent_id, PDO::PARAM_INT ) ;
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}


		/*
		** Détruit l'association dans la table de liaison entre les groupes et
		** les entités.
		*/
      if ( $_Access_ENT_GRP == 1 ) {
         if ( ! $Result = $this->prepare( 'DELETE ' .
          'FROM engr_entities_groups ' .
          'WHERE ent_id = :ent_id' ) ) {
            $this->rollBack();
			
            $Error = $Result->errorInfo();
            throw new Exception( $Error[ 2 ], $Error[ 1 ] );
         }

         $Result->bindParam( ':ent_id', $ent_id, PDO::PARAM_INT ) ;
		
         if ( ! $Result->execute() ) {
            $this->rollBack();

            $Error = $Result->errorInfo();
            throw new Exception( $Error[ 2 ], $Error[ 1 ] );
         }
      }


		/*
		** Détruit l'association dans la table de liaison entre les identités et
		** les entités.
		*/
      if ( $_Access_IDN_ENT == 1 ) {
         if ( ! $Result = $this->prepare( 'DELETE ' .
          'FROM iden_identities_entities ' .
          'WHERE ent_id = :ent_id' ) ) {
            $this->rollBack();
			
            $Error = $Result->errorInfo();
            throw new Exception( $Error[ 2 ], $Error[ 1 ] );
         }

         $Result->bindParam( ':ent_id', $ent_id, PDO::PARAM_INT ) ;
		
         if ( ! $Result->execute() ) {
            $this->rollBack();

            $Error = $Result->errorInfo();
            throw new Exception( $Error[ 2 ], $Error[ 1 ] );
         }
      }


		/*
		** Efface la référence de cette entité dans les identités.
		*/
		if ( ! $Result = $this->prepare( 'UPDATE ' .
		 'idn_identities ' .
		 'SET ent_id = NULL ' .
		 'WHERE ent_id = :ent_id' ) ) {
			$this->rollBack();
			
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		$Result->bindParam( ':ent_id', $ent_id, PDO::PARAM_INT ) ;
		
		if ( ! $Result->execute() ) {
			$this->rollBack();

			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}


		// Sauvegarde l'ensemble des modifications.
		$this->commit();
 
 		return true;
	}


	public function isAssociated( $ent_id, $Table ) {
	/**
	* Vérifie si l'Entité est associé.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-13
	*
	* @param[in] $ent_id Identifiant de l'entité à contrôler
	* @param[in] $Table Permet de spécifier sur quelle table le contrôle est fait
	*
	* @return Renvoi vrai si l'Entité est associée sur la table spécifiée
	*/
		switch( strtoupper( $Table ) ) {
		 case 'GRP':
			$Request = 'SELECT ' .
			 'count(*) ' .
			 'FROM engr_entities_groups ' .
			 'WHERE ent_id = :ent_id ' ;
			break;

		 case 'IDEN':
			$Request = 'SELECT ' .
			 'count(*) ' .
			 'FROM iden_identities_entities ' .
			 'WHERE ent_id = :ent_id ' ;
			break;

		 case 'IDN':
			$Request = 'SELECT ' .
			 'count(*) ' .
			 'FROM idn_identities ' .
			 'WHERE ent_id = :ent_id ' ;
			break;
		}

		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->bindParam( ':ent_id', $ent_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Valeur = $Result->fetch() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( $Valeur[ 0 ] == 0 ) return false ;
		
		return true ;
	}


	public function total() {
	/**
	* Calcul le nombre total d'Entités.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-13
	*
	* @return Renvoi le nombre total d'Entités de stocker en base
	*/
		$Request = 'SELECT ' .
		 'count(*) AS total ' .
		 'FROM ent_entities ' .
		 'WHERE ent_logical_delete = 0 ';

		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Occurrence = $Result->fetchObject() ;
		
		return $Occurrence->total;
	}


} // Fin class IICA_Entities

?>