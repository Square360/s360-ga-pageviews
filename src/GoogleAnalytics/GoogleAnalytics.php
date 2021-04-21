<?php

namespace Drupal\s360_ga_pageviews\GoogleAnalytics;

use Google_Client;
use Google_Service_AnalyticsReporting;
use Google_Service_AnalyticsReporting_DateRange;
use Google_Service_AnalyticsReporting_Metric;
use Google_Service_AnalyticsReporting_Dimension;
use Google_Service_AnalyticsReporting_ReportRequest;
use Google_Service_AnalyticsReporting_GetReportsRequest;
use Google_Service_AnalyticsReporting_GetReportsResponse;

/**
 * Google.
 */
class GoogleAnalytics {

  const GA_DIMENSIONS = 'ga:pagePath';
  const GA_METRICS = 'ga:pageviews';
  const GA_BASE_FILTER_EXPRESSION = 'ga:pagePath!@/?;ga:pagePath!@404;ga:pagePath!@403;';

  /**
   * The domain config.
   *
   * @var array
   *   The config.
   */
  private $config;

  /**
   * Undocumented function.
   */
  public function __construct(array $config) {
    $this->config = $config;
  }

  /**
   * Initializes an Analytics Reporting API V4 service object.
   *
   * @return Google_Service_AnalyticsReporting
   *   An authorized Analytics Reporting API V4 service object.
   */
  public function initializeAnalytics() {
    // Use the developers console and download your service account
    // credentials in JSON format. Place them in this directory or
    // change the key file location if necessary.
    $key_file_location = S360_GA_PAGEVIEWS_PRIVATE_FOLDER_PATH . '/' . S360_GA_PAGEVIEWS_GA_KEY_FILE_FOLDER . '/' . $this->config['key_file'];

    // Create and configure a new client object.
    $client = new Google_Client();
    $client->setApplicationName($this->config['name'] . ' Reporting');
    $client->setAuthConfig($key_file_location);
    $client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);

    $analytics = new Google_Service_AnalyticsReporting($client);

    return $analytics;
    // $this->getReport($analytics);
  }

  /**
   * Queries the Analytics Reporting API V4.
   *
   * @param Google_Service_AnalyticsReporting $analytics
   *   An authorized Analytics Reporting API V4 service object.
   *
   * @return Google_Service_AnalyticsReporting_GetReportsResponse
   *   The Analytics Reporting API V4 response.
   */
  public function getReport(Google_Service_AnalyticsReporting $analytics) {
    // Create the DateRange object.
    $date_range = new Google_Service_AnalyticsReporting_DateRange();
    $date_range->setStartDate($this->config['look_back'] . 'daysAgo');
    $date_range->setEndDate('today');

    // Create the Metrics object.
    $sessions = new Google_Service_AnalyticsReporting_Metric();
    $sessions->setExpression($this::GA_METRICS);
    $sessions->setAlias('pageviews');

    $dimensions = new Google_Service_AnalyticsReporting_Dimension();
    $dimensions->setName($this::GA_DIMENSIONS);

    // Create the ReportRequest object.
    $request = new Google_Service_AnalyticsReporting_ReportRequest();
    $request->setViewId($this->config['view_id']);
    $request->setDateRanges($date_range);
    $request->setMetrics([$sessions]);
    $request->setDimensions([$dimensions]);
    $request->setFiltersExpression($this::GA_BASE_FILTER_EXPRESSION . 'ga:pagePath!@resource-search;ga:pagePath!=/;ga:pagePath!@/center;ga:pagePath!@/news;ga:pagePath!@/event');

    $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
    $body->setReportRequests([$request]);

    return $analytics->reports->batchGet($body);
  }

  /**
   * Take the raw Google Analytics report data and normalizes it.
   *
   * @param Google_Service_AnalyticsReporting_GetReportsResponse $reports
   *   The report.
   *
   * @return array
   *   Array.
   */
  public function normalizeReport(Google_Service_AnalyticsReporting_GetReportsResponse $reports) {
    foreach ($reports as $report) {
      $rows = $report->getData()->getRows();

      $page_report = [];
      $previous_page_path = '';

      foreach ($rows as $row) {
        $dimensions = $row->getDimensions();
        $metrics = $row->getMetrics();
        $metrics = reset($metrics)->getValues();
        $pageviews = (int) reset($metrics);

        $page_path = reset($dimensions);
        $page_path_parts = explode('?', $page_path);
        $current_page_path = $page_path_parts[0];

        $raw_report[$page_path] = $pageviews;

        if ($previous_page_path !== $current_page_path) {
          $page_report[$current_page_path] = $pageviews;
        }
        else {
          $page_report[$current_page_path] = $page_report[$current_page_path] + $pageviews;
        }

        $previous_page_path = $current_page_path;
      }

      return $page_report;
    }
  }

}
