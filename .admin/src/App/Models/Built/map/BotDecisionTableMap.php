<?php

namespace App\map;

use \RelationMap;
use \TableMap;


/**
 * This class defines the structure of the 'bot_decision' table.
 *
 *
 *
 * This map class is used by Propel to do runtime db structure discovery.
 * For example, the createSelectSql() method checks the type of a given column used in an
 * ORDER BY clause to know whether it needs to apply SQL to make the ORDER BY case-insensitive
 * (i.e. if it's a text column type).
 *
 * @package    propel.generator..map
 */
class BotDecisionTableMap extends TableMap
{

    /**
     * The (dot-path) name of this class
     */
    const CLASS_NAME = '.map.BotDecisionTableMap';

    /**
     * Initialize the table attributes, columns and validators
     * Relations are not initialized by this method since they are lazy loaded
     *
     * @return void
     * @throws PropelException
     */
    public function initialize()
    {
        // attributes
        $this->setName('bot_decision');
        $this->setPhpName('BotDecision');
        $this->setClassname('App\\BotDecision');
        $this->setPackage('');
        $this->setUseIdGenerator(true);
        // columns
        $this->addPrimaryKey('id_bot_decision', 'IdBotDecision', 'INTEGER', true, 11, null);
        $this->addForeignKey('id_grid_run', 'IdGridRun', 'INTEGER', 'grid_run', 'id_grid_run', true, 11, null);
        $this->addColumn('source', 'Source', 'ENUM', true, null, 'Claude');
        $this->getColumn('source', false)->setValueSet(array (
  0 => 'Claude',
  1 => 'Cron',
  2 => 'Manual',
));
        $this->addColumn('p_low', 'PLow', 'DECIMAL', true, 18, null);
        $this->addColumn('p_high', 'PHigh', 'DECIMAL', true, 18, null);
        $this->addColumn('n_levels', 'NLevels', 'INTEGER', true, 10, null);
        $this->addColumn('deploy_pct', 'DeployPct', 'INTEGER', false, 10, null);
        $this->addColumn('reason', 'Reason', 'VARCHAR', false, 500, null);
        $this->addColumn('price_at', 'PriceAt', 'DECIMAL', false, 18, null);
        $this->addColumn('realized_before', 'RealizedBefore', 'DECIMAL', false, 18, 0);
        $this->addColumn('eval_status', 'EvalStatus', 'ENUM', true, null, 'Pending');
        $this->getColumn('eval_status', false)->setValueSet(array (
  0 => 'Pending',
  1 => 'Scored',
));
        $this->addColumn('eval_at', 'EvalAt', 'TIMESTAMP', false, null, null);
        $this->addColumn('applied_at', 'AppliedAt', 'TIMESTAMP', false, null, null);
        $this->addColumn('cycles_delta', 'CyclesDelta', 'INTEGER', false, 10, null);
        $this->addColumn('realized_delta', 'RealizedDelta', 'DECIMAL', false, 18, null);
        $this->addColumn('price_move_pct', 'PriceMovePct', 'DECIMAL', false, 9, null);
        $this->addColumn('verdict', 'Verdict', 'ENUM', false, null, null);
        $this->getColumn('verdict', false)->setValueSet(array (
  0 => 'Win',
  1 => 'Flat',
  2 => 'Loss',
  3 => 'Superseded',
));
        $this->addColumn('date_creation', 'DateCreation', 'TIMESTAMP', false, null, null);
        $this->addColumn('date_modification', 'DateModification', 'TIMESTAMP', false, null, null);
        $this->addForeignKey('id_group_creation', 'IdGroupCreation', 'INTEGER', 'authy_group', 'id_authy_group', false, null, null);
        $this->addForeignKey('id_creation', 'IdCreation', 'INTEGER', 'authy', 'id_authy', false, null, null);
        $this->addForeignKey('id_modification', 'IdModification', 'INTEGER', 'authy', 'id_authy', false, null, null);
        // validators
        $this->addValidator('id_bot_decision', 'required', 'propel.validator.RequiredValidator', '', ('BotDecision_IdBotDecision_required'));
        $this->addValidator('id_bot_decision', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('BotDecision_IdBotDecision_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('id_grid_run', 'required', 'propel.validator.RequiredValidator', '', ('BotDecision_IdGridRun_required'));
        $this->addValidator('id_grid_run', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('BotDecision_IdGridRun_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('source', 'required', 'propel.validator.RequiredValidator', '', ('BotDecision_Source_required'));
        $this->addValidator('source', 'type', 'propel.validator.TypeValidator', 'string', ('BotDecision_Source_type_string'));
        $this->addValidator('p_low', 'required', 'propel.validator.RequiredValidator', '', ('BotDecision_PLow_required'));
        $this->addValidator('p_high', 'required', 'propel.validator.RequiredValidator', '', ('BotDecision_PHigh_required'));
        $this->addValidator('n_levels', 'required', 'propel.validator.RequiredValidator', '', ('BotDecision_NLevels_required'));
        $this->addValidator('n_levels', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('BotDecision_NLevels_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('deploy_pct', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('BotDecision_DeployPct_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('reason', 'type', 'propel.validator.TypeValidator', 'string', ('BotDecision_Reason_type_string'));
        $this->addValidator('eval_status', 'required', 'propel.validator.RequiredValidator', '', ('BotDecision_EvalStatus_required'));
        $this->addValidator('eval_status', 'type', 'propel.validator.TypeValidator', 'string', ('BotDecision_EvalStatus_type_string'));
        $this->addValidator('eval_at', 'match', 'propel.validator.MatchValidator', '', ('BotDecision_EvalAt_match'));
        $this->addValidator('applied_at', 'match', 'propel.validator.MatchValidator', '', ('BotDecision_AppliedAt_match'));
        $this->addValidator('cycles_delta', 'match', 'propel.validator.MatchValidator', '/^(?:[0-9]*|null)$/', ('BotDecision_CyclesDelta_match_/^(?:[0-9]*|null)$/'));
        $this->addValidator('verdict', 'type', 'propel.validator.TypeValidator', 'string', ('BotDecision_Verdict_type_string'));
    } // initialize()

    /**
     * Build the RelationMap objects for this table relationships
     */
    public function buildRelations()
    {
        $this->addRelation('GridRun', 'App\\GridRun', RelationMap::MANY_TO_ONE, array('id_grid_run' => 'id_grid_run', ), 'CASCADE', null);
        $this->addRelation('AuthyGroup', 'App\\AuthyGroup', RelationMap::MANY_TO_ONE, array('id_group_creation' => 'id_authy_group', ), null, null);
        $this->addRelation('AuthyRelatedByIdCreation', 'App\\Authy', RelationMap::MANY_TO_ONE, array('id_creation' => 'id_authy', ), null, null);
        $this->addRelation('AuthyRelatedByIdModification', 'App\\Authy', RelationMap::MANY_TO_ONE, array('id_modification' => 'id_authy', ), null, null);
    } // buildRelations()

    /**
     *
     * Gets the list of behaviors registered for this table
     *
     * @return array Associative array (name => parameters) of behaviors
     */
    public function getBehaviors()
    {
        return array(
            'GoatCheese' =>  array (
  'i18n_langs' => '["en_US"]',
  'logo_url' => '',
  'set_parent_table' => 'grid_run',
  'set_child_colunms' => '{"id_grid_run":["label"]}',
  'set_order_child_list_columns' => '[["date_creation","DESC"]]',
  'set_readonly_columns' => '["source","p_low","p_high","n_levels","reason","price_at","realized_before","eval_status","eval_at","applied_at","cycles_delta","realized_delta","price_move_pct","verdict"]',
),
            'add_validator' =>  array (
),
            'add_tablestamp' =>  array (
  'create_column' => 'date_creation',
  'update_column' => 'date_modification',
  'create_id_column' => 'id_creation',
  'group_id_column' => 'id_group_creation',
  'update_id_column' => 'id_modification',
  'exclude' => 'none',
  'foreign_keys' => 'all',
),
        );
    } // getBehaviors()

} // BotDecisionTableMap
