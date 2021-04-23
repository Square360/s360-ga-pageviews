<?php

/**
 * @file
 * views.inc.php
 */

/**
 * Implements hook_views_data().
 */
function s360_ga_pageviews_views_data() {
  $data = [];
  $data[S360_GA_PAGEVIEWS_TABLE_NAME] = [];
  $data[S360_GA_PAGEVIEWS_TABLE_NAME]['table'] = [];
  $data[S360_GA_PAGEVIEWS_TABLE_NAME]['table']['group'] = t('GA Pageviews');
  $data[S360_GA_PAGEVIEWS_TABLE_NAME]['table']['provider'] = 's360_ga_pageviews';
  $data[S360_GA_PAGEVIEWS_TABLE_NAME]['table']['base'] = [
    // Identifier (primary) field in this table for Views.
    'field' => 'id',
    'title' => t('GA Pageviews'),
    'help' => t('Table contains page view counts based on path aliases.'),
    'weight' => -10,
  ];

  $data[S360_GA_PAGEVIEWS_TABLE_NAME]['table']['join']['node_field_data'] = [
    'left_table' => 'node',
    'left_field' => 'nid',
    'field' => 'nid',
  ];

  $data[S360_GA_PAGEVIEWS_TABLE_NAME]['table']['join']['taxonomy_term_field_data'] = [
    'left_table' => 'taxonomy_term_data',
    'left_field' => 'tid',
    'field' => 'tid',
  ];

  $data[S360_GA_PAGEVIEWS_TABLE_NAME]['nid'] = [
    'title' => t('Content'),
    'help' => t('Relate GA Pageviews to node content.'),
    'relationship' => [
      // Views name of the table to join to for the relationship.
      'base' => 'node_field_data',
      // Database field name in the other table to join on.
      'base field' => 'nid',
      // ID of relationship handler plugin to use.
      'id' => 'standard',
    ],
  ];

  $data[S360_GA_PAGEVIEWS_TABLE_NAME]['tid'] = [
    'title' => t('Taxonomy terms'),
    'help' => t('Relate GA Pageviews to taxonomy terms.'),
    'relationship' => [
      // Views name of the table to join to for the relationship.
      'base' => 'taxonomy_term_data',
      // Database field name in the other table to join on.
      'base field' => 'tid',
      // ID of relationship handler plugin to use.
      'id' => 'standard',
    ],
  ];

  // Expose the field, sort, filter and argument for pageviews.
  $data[S360_GA_PAGEVIEWS_TABLE_NAME]['pageviews'] = [
    'title' => t('Pageviews'),
    'help' => t('Total number of page views by path.'),
    'field' => [
      'id' => 'numeric',
    ],
    'sort' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'numeric',
    ],
    'argument' => [
      'id' => 'numeric',
    ],
  ];

  // Expose the field, sort, filter and argument for page_path.
  $data[S360_GA_PAGEVIEWS_TABLE_NAME]['page_path'] = [
    'title' => t('Page Path'),
    'help' => t('The alias'),
    'field' => [
      'id' => 'standard',
    ],
    'sort' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'argument' => [
      'id' => 'string',
    ],
  ];

  // Expose the field, sort, filter and argument for domain.
  $data[S360_GA_PAGEVIEWS_TABLE_NAME]['domain'] = [
    'title' => t('Domain'),
    'help' => t('What domain the page belongs to'),
    'field' => [
      'id' => 'standard',
    ],
    'sort' => [
      'id' => 'standard',
    ],
    'filter' => [
      'id' => 'string',
    ],
    'argument' => [
      'id' => 'string',
    ],
  ];

  return $data;
}
