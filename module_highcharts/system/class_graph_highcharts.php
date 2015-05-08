<?php
/*"******************************************************************************************************
*   (c) 2013-2014 by Kajona, www.kajona.de                                                              *
*       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt                                 *
*-------------------------------------------------------------------------------------------------------*
*	$Id$                                      *
********************************************************************************************************/

/**
 * This class could be used to create graphs based on the highcharts API.
 * Highcharts renders charts on the client side.
 *
 * @package module_highcharts
 * @since 4.6
 * @author stefan.meyer1@yahoo.de
 */
class class_graph_highcharts implements interface_graph {

    private $intWidth = 700;
    private $intHeight = 350;

    private $arrXAxisTickLabels = null;
    private $arrYAxisTickLabels = null;
    private $intNrOfWrittenLabelsXAxis = null;
    private $intNrOfWrittenLabelsYAxis = null;
    private $arrSeriesColors =  null;

    private $bitIsHorizontalBar = false;
    private $bitXAxisLabelsInvisible = false;
    private $bitYAxisLabelsInvisible = false;


    /**
     * contains all series data per added chart
     * @var class_graph_highcharts_seriesdata[]
     */
    private $arrSeriesData = array(); //

    // array which contains all used highchart-Options.
    private $arrOptions = array(
        "credits" => array(
            "enabled" => false
        ),
        "tooltip" => array(
            "followPinter" => true
        ),

        "colors" => array("#8bbc21", "#2f7ed8", "#f28f43", "#1aadce", "#77a1e5", "#0d233a", "#c42525", "#a6c96a", "#910000"),

        "chart" => array(
            "backgroundColor" => null,
            "height" => null,
            "width" => null,
            "style" => array(
                "fontFamily" => "'Open Sans', Helvetica, Arial, sans-serif"
            )
        ),

        "legend" => array(
            "enabled" => null,
            "itemStyle" => array()
        ),

        "title" => "",

        "xAxis" => array(
            "title" => array(
                "text" => "",
                "style" => array()
            ),
            "categories" => null,
            "labels" => array(
                "rotation" => null,
                "style" => array(),
                "enabled" => true
            ),
            "tickAmount" => null
        ),

        "yAxis" => array(
            "title" => array(
                "text" => "",
                "style" => array()
            ),
            "categories" => null,
            "labels" => array(
                "rotation" => null,
                "style" => array(),
                "enabled" => true
            ),
            "tickAmount" => null
        ),

        "plotOptions" => array(
            "column" => array(
                "stacking" => null
            ),
            "bar" => array(
                "stacking" => null
            ),
            "events" => array(
                "click" => null
            )
        ),

        "series" => array(),

        "exporting" => array(
            "enabled" => false
        )
    );


    /**
     * Checks if the chart contains the given chart type
     *
     * @param $intChartType
     *
     * @return bool
     */
    function containsChartType($intChartType) {
        foreach($this->arrSeriesData as $objSeriesData) {
            if($objSeriesData->getIntChartType() === $intChartType)
                return true;
        }
        return false;
    }

    /**
     * Gets series objects of the given chart type
     *
     * @param array $arrChartTypes
     *
     * @return class_graph_highcharts_seriesdata[]
     */
    private function getSeriesObjectsByChartType(array $arrChartTypes) {
        $arrSeriesObjects = array();

        foreach($this->arrSeriesData as $objSeriesData) {
            if(in_array($objSeriesData->getIntChartType(), $arrChartTypes)) {
                $arrSeriesObjects[] = $objSeriesData;
            }
        }
        return $arrSeriesObjects;
    }

    /**
     * Used to create a bar-chart.
     * For each set of bar-values you can call this method once.
     * This means, calling this method twice creates a grouped bar chart
     * A sample-code could be:
     *  $objGraph = new class_graph();
     *  $objGraph->setStrXAxisTitle("x-axis");
     *  $objGraph->setStrYAxisTitle("y-axis");
     *  $objGraph->setStrGraphTitle("Test Graph");
     *  $objGraph->addBarChartSet(array(1,2,4,5) "serie 1");
     *
     * @param array $arrValues see the example above for the internal array-structure
     * @param string $strLegend
     * @param bool $bitWriteValues Enables the rendering of values on top of the graphs
     *
     * @throws class_exception
     */
    public function addBarChartSet($arrValues, $strLegend, $bitWriteValues = false) {
        $arrDataPoints = class_graph_commons::convertArrValuesToDataPointArray($arrValues);

        if($this->containsChartType(class_graph_highcharts_charttype::PIE)) {
            throw new class_exception("Chart already contains a Pie chart. Combinations of pie charts and bar charts are not allowed", class_exception::$level_ERROR);
        }
        if($this->containsChartType(class_graph_highcharts_charttype::STACKEDBAR)) {
            throw new class_exception("Chart already contains a stacked bar chart. Combinations of bar charts and stacked bar charts are not allowed", class_exception::$level_ERROR);
        }

        $objSeriesData = new class_graph_highcharts_seriesdata(class_graph_highcharts_charttype::BAR, count($this->arrSeriesData), $this->arrOptions);
        $objSeriesData->setArrDataPoints($arrDataPoints);
        $objSeriesData->setStrSeriesLabel($strLegend);
        $objSeriesData->setBitWriteValues($bitWriteValues);

        $this->arrSeriesData[] = $objSeriesData;
    }


    /**
     * Used to create a stacked bar-chart.
     * For each set of bar-values you can call this method once.
     * A sample-code could be:
     *  $objGraph = new class_graph();
     *  $objGraph->setStrXAxisTitle("x-axis");
     *  $objGraph->setStrYAxisTitle("y-axis");
     *  $objGraph->setStrGraphTitle("Test Graph");
     *  $objGraph->addStackedBarChartSet(array(1,2,4,5) "serie 1");
     *  $objGraph->addStackedBarChartSet(array(1,2,4,5) "serie 2");
     *
     * @param array $arrValues see the example above for the internal array-structure
     * @param string $strLegend
     * @param string $strLegend
     * @param bool $bitIsHorizontal
     *
     * @throws class_exception
     */
    public function addStackedBarChartSet($arrValues, $strLegend, $bitIsHorizontal = false) {
        $arrDataPoints = class_graph_commons::convertArrValuesToDataPointArray($arrValues);

        $barChartType =  class_graph_highcharts_charttype::STACKEDBAR;
        if($bitIsHorizontal) {
            $barChartType =  class_graph_highcharts_charttype::STACKEDBAR_HORIZONTAL;
        }

        if($this->containsChartType(class_graph_highcharts_charttype::PIE)) {
            throw new class_exception("Chart already contains a Pie chart. Combinations of pie charts and stacked bar charts are not allowed", class_exception::$level_ERROR);
        }
        if($this->containsChartType(class_graph_highcharts_charttype::LINE)) {
            throw new class_exception("Chart already contains a line chart. Combinations of line charts and stacked bar charts are not allowed", class_exception::$level_ERROR);
        }
        if($this->containsChartType(class_graph_highcharts_charttype::BAR)) {
            throw new class_exception("Chart already contains a bar chart. Combinations of bar charts and stacked bar charts are not allowed", class_exception::$level_ERROR);
        }
        if($bitIsHorizontal && $this->containsChartType(class_graph_highcharts_charttype::STACKEDBAR)) {
            throw new class_exception("Chart already contains a horizontal bar chart. Combinations of stacked bar charts and horizontal stacked bar charts are not allowed", class_exception::$level_ERROR);
        }
        if(!$bitIsHorizontal && $this->containsChartType(class_graph_highcharts_charttype::STACKEDBAR_HORIZONTAL)) {
            throw new class_exception("Chart already contains a bar chart. Combinations of stacked bar charts and horizontal stacked bar charts are not allowed", class_exception::$level_ERROR);
        }

        $objSeriesData = new class_graph_highcharts_seriesdata($barChartType, count($this->arrSeriesData), $this->arrOptions);
        $objSeriesData->setArrDataPoints($arrDataPoints);
        $objSeriesData->setStrSeriesLabel($strLegend);

        $this->arrSeriesData[] = $objSeriesData;
    }

    /**
     * Registers a new plot to the current graph. Works in line-plot-mode only.
     * Add a set of linePlot to a graph to get more then one line.
     * If you created a bar-chart before, it it is possible to add line-plots on top of
     * the bars. Nevertheless, the scale is calculated out of the bars, so make
     * sure to remain inside the visible range!
     * A sample-code could be:
     *  $objGraph = new class_graph();
     *  $objGraph->setStrXAxisTitle("x-axis");
     *  $objGraph->setStrYAxisTitle("y-axis");
     *  $objGraph->setStrGraphTitle("Test Graph");
     *  $objGraph->addLinePlot(array(1,4,6,7,4), "serie 1");
     *
     * @param array $arrValues e.g. array(1,3,4,5,6)
     * @param string $strLegend the name of the single plot
     *
     * @throws class_exception
     */
    public function addLinePlot($arrValues, $strLegend) {
        $arrDataPoints = class_graph_commons::convertArrValuesToDataPointArray($arrValues);

        if($this->containsChartType(class_graph_highcharts_charttype::PIE)) {
            throw new class_exception("Chart already contains a pie chart. Combinations of pie charts and line charts are not allowed", class_exception::$level_ERROR);
        }
        if($this->containsChartType(class_graph_highcharts_charttype::STACKEDBAR)) {
            throw new class_exception("Chart already contains a stacked bar chart. Combinations of stacked bar charts and line charts are not allowed", class_exception::$level_ERROR);
        }

        $objSeriesData = new class_graph_highcharts_seriesdata(class_graph_highcharts_charttype::LINE, count($this->arrSeriesData), $this->arrOptions);
        $objSeriesData->setArrDataPoints($arrDataPoints);
        $objSeriesData->setStrSeriesLabel($strLegend);

        $this->arrSeriesData[] = $objSeriesData;
    }

    /**
     * Creates a new pie-chart. Pass the values as the first param. If
     * you want to use a legend and / or Colors use the second and third param.
     * Make sure the array have the same number of elements, ohterwise they won't
     * be uses.
     * A sample-code could be:
     *  $objChart = new class_graph();
     *  $objChart->setStrGraphTitle("Test Pie Chart");
     *  $objChart->createPieChart(array(2,6,7,3), array("val 1", "val 2", "val 3", "val 4"));
     *
     * @param array $arrValues
     * @param array $arrLegends
     *
     * @throws class_exception
     */
    public function createPieChart($arrValues, $arrLegends) {
        $arrDataPoints = class_graph_commons::convertArrValuesToDataPointArray($arrValues);

        if($this->containsChartType(class_graph_highcharts_charttype::LINE)
            || $this->containsChartType(class_graph_highcharts_charttype::BAR)
            || $this->containsChartType(class_graph_highcharts_charttype::STACKEDBAR)
        ) {
            throw new class_exception("Chart already contains either a line, bar or stacked bar chart. Combinations of pie charts with other charts are not allowed", class_exception::$level_ERROR);
        }
        if($this->containsChartType(class_graph_highcharts_charttype::PIE)) {
            throw new class_exception("Chart already contains either a pie chart.Only one pie chart per chart is allowed", class_exception::$level_ERROR);
        }

        $objSeriesData = new class_graph_highcharts_seriesdata(class_graph_jqplot_charttype::PIE, count($this->arrSeriesData), $this->arrOptions);
        $objSeriesData->setArrDataPoints($arrDataPoints);

        $this->setArrXAxisTickLabels($arrLegends);
        $this->arrSeriesData[] = $objSeriesData;
    }

    /**
     * Does the magic. Creates all necessary stuff and finally
     * sends the graph directly (!!!) to the browser.
     * Execution should be terminated afterwards.
     * <b>Please note that not all chart-engines support this method.</b>
     *
     * @deprecated use interface_graph::renderGraph() instead
     */
    public function showGraph() {
         $this->renderGraph();
    }

    /**
     * Does the magic. Creates all necessary stuff and finally
     * saves the graph to the specified filename
     * <b>Please note that not all chart-engines support this method.</b>
     *
     * @deprecated use interface_graph::renderGraph() instead
     */
    public function saveGraph($strFilename) {
        //not supported
    }

    /**
     * Common way to get a chart. The engine should save the chart
     * to the filesystem (if required) and returns the chart with a complete
     * code to embed the chart into a html-page.
     * Please be aware that the method may return a large amount of code depending on
     * the type of engine - from a simple img-tag up to a full js-logic.
     *
     * @since 4.0
     * @throws class_exception
     * @return mixed
     */
    public function renderGraph() {
        if(count($this->arrSeriesData) == 0) {
            throw new class_exception("Chart not initialized yet", class_exception::$level_ERROR);
        }

        $this->preGraphGeneration();

        //create id's
        $strSystemId = generateSystemid();
        $strChartId =  "chart_".$strSystemId;

        //create div where the chart is being put
        $strReturn = "<div id=\"$strChartId\" style=\"width:".$this->intWidth."px; height:".$this->intHeight."px;\"></div>";

        //create the data array and options object for the highcharts method
        $strOptions = $this->strCreateJSOptions();

        $strCoreDirectory = class_resourceloader::getInstance()->getCorePathForModule("module_highcharts");

        $strReturn .= "<script type='text/javascript'>
                KAJONA.admin.loader.loadFile(['{$strCoreDirectory}/module_highcharts/admin/scripts/js/highcharts/highcharts.js'], function() {
                    KAJONA.admin.loader.loadFile([
                    '{$strCoreDirectory}/module_highcharts/admin/scripts/js/highcharts/modules/exporting.js',
                    '{$strCoreDirectory}/module_highcharts/admin/scripts/js/custom/highcharts.custom.js',
                    ], function() {
                        var objChart_$strChartId = new KAJONA.admin.highchartsHelper.objChartWrapper('$strChartId', $strOptions);
                        objChart_$strChartId.render();
                    });
                });
        </script>";

        return $strReturn;
    }


    private function preGraphGeneration() {
        if($this->bitIsHorizontalBar) {
            $arrHorizontalCharts = $this->getSeriesObjectsByChartType(array(class_graph_highcharts_charttype::BAR, class_graph_highcharts_charttype::STACKEDBAR));

            foreach($arrHorizontalCharts as $objChart) {
                $arrOptions = $objChart->getArrSeriesOptions();
                $arrOptions["type"]="bar";
                $objChart->setArrSeriesOptions($arrOptions);
            }
        }
    }

    /**
     * Create a deep copy of the given array containing no elements with null values.
     * Also removes empty arrays from the given array.
     *
     * @param $arrInput
     *
     * @return array|null
     */
    private function cleanUpArray($arrInput) {
        // If it is an element, then just return it
        if (!is_array($arrInput)) {
            return $arrInput;
        }
        $arrNonEmptyItems = array();

        foreach ($arrInput as $key => $value) {
            // Ignore null values
            if($value!==null) {
                // Use recursion to evaluate values
                $returnValue = $this->cleanUpArray($value);
                if($returnValue!==null)
                    $arrNonEmptyItems[$key] = $this->cleanUpArray($value);
            }
        }

        //Only return the array if it contains elements, else null
        if(count($arrNonEmptyItems)>0)
            return $arrNonEmptyItems;
        else
            return null;
    }

    private function strCreateJSOptions() {
        /*
        Sort the series data array
        Bar charts must be plotted before line charts
        Also consider the order in which the series were added)
        */
        uasort($this->arrSeriesData, function(class_graph_highcharts_seriesdata $objLeft, class_graph_highcharts_seriesdata $objRight) {
            $intLeft = $objLeft->getIntChartType();
            $intRight = $objRight->getIntChartType();

            if($intLeft == $intRight) {
                //consider order in which the series was added
                if($objLeft->getIntSeriesDataOrder() < $objRight->getIntSeriesDataOrder()) {
                    return -1;
                }
                else {
                    return 1;
                }
            }
            if($intLeft < $intRight)  return -1;
            if($intLeft > $intRight)  return 1;
        });

        //add series options of each series to $arrOptions
        foreach($this->arrSeriesData as $objSeriesData) {
            $arrOptions = $objSeriesData->getArrSeriesOptions();
            $arrOptions["data"] = $this->strCreateJSDataArray($objSeriesData);

            $this->arrOptions["series"][] = $arrOptions;
        }

        //remove all values which are null
        $this->arrOptions = $this->cleanUpArray($this->arrOptions);
        $strEncode =  json_encode($this->arrOptions);
        $strEncode = preg_replace('/(\\"click\\":\\"(.*?)\\")/', "\"click\":$2", $strEncode);//remove '"' where an click event is being executed


        return $strEncode;
    }

    private function strCreateJSDataArray($objSeriesData) {
        $arrData = array();
        $arrDataPoints = $objSeriesData->getArrDataPoints();

        /** @var class_graph_datapoint $objDataPoint */
        foreach($arrDataPoints as $objDataPoint) {
            $arrPoint = array(
                "events" => array(
                    "click" => null
                ),
                "y" => null,
                "name" => null
            );

            $arrPoint["y"] = $objDataPoint->getFloatValue();
            if($objDataPoint->getObjActionHandler() !== null) {
                $arrPoint["events"]["click"] = $objDataPoint->getObjActionHandler();
            }

            $arrData[] = $arrPoint;
        }

        //for pie charts name must included into the point
        if($this->containsChartType(class_graph_highcharts_charttype::PIE)) {
            foreach($arrData as  $intIndex => &$arrPoint) {
                $arrPoint["name"] = $this->arrXAxisTickLabels[$intIndex];
            }
        }


        return $arrData;
    }

    /**
     * Set the title of the x-axis
     *
     * @param string $strTitle
     */
    public function setStrXAxisTitle($strTitle) {
        $this->arrOptions["xAxis"]["title"]["text"] = $strTitle;
    }

    /**
     * Set the title of the y-axis
     *
     * @param string $strTitle
     */
    public function setStrYAxisTitle($strTitle) {
        $this->arrOptions["yAxis"]["title"]["text"] = $strTitle;
    }

    /**
     * Set the title of the graph
     *
     * @param string $strTitle
     */
    public function setStrGraphTitle($strTitle) {
        $this->arrOptions["title"]["text"] = $strTitle;
    }

    /**
     * Set the color of the margin-areas, so the color of the area not being
     * the plot-area.
     * In most cases this is the background.
     *
     * @param string $strColor in hex-values: #ccddee
     */
    public function setStrBackgroundColor($strColor) {
        $this->arrOptions["chart"]["backgroundColor"] = $strColor;
    }

    /**
     * Set the total width of the chart
     *
     * @param int $intWidth
     */
    public function setIntWidth($intWidth) {
        $this->intWidth = $intWidth;
        $this->arrOptions["chart"]["width"] = $intWidth;
    }

    /**
     * Set the total height of the chart
     *
     * @param int $intHeight
     */
    public function setIntHeight($intHeight) {
        $this->intHeight = $intHeight;
        $this->arrOptions["chart"]["height"] = $intHeight;
    }

    /**
     * Set the labels to be used for the x-axis.
     * Make sure to set them before adding datasets!
     *
     * @param array $arrXAxisTickLabels array of string to be used as labels
     * @param int $intNrOfWrittenLabels the amount of x-axis labels to be printed
     */
    public function setArrXAxisTickLabels($arrXAxisTickLabels, $intNrOfWrittenLabels = 12) {
        $this->intNrOfWrittenLabelsXAxis = $intNrOfWrittenLabels;
        $this->arrXAxisTickLabels = $arrXAxisTickLabels;

        $this->arrOptions["xAxis"]["categories"] = $arrXAxisTickLabels;
        $this->arrOptions["xAxis"]["tickAmount"] = $intNrOfWrittenLabels;
    }

    /**
     * Sets if to render a legend or not
     *
     * @param bool $bitRenderLegend
     */
    public function setBitRenderLegend($bitRenderLegend) {
        $this->arrOptions["legend"]["enabled"] = $bitRenderLegend;
    }

    /**
     * Set the font to be used in the chart
     *
     * @param string $strFont
     */
    public function setStrFont($strFont) {
        $this->arrOptions["chart"]["style"]["fontFamily"] = $strFont;
    }

    /**
     * Set the color of the fonts used in the chart
     *
     * @param string $strFontColor
     */
    public function setStrFontColor($strFontColor) {
        $this->arrOptions["chart"]["style"]["color"] = $strFontColor;
        $this->arrOptions["title"]["style"]["color"] = $strFontColor;

        $this->arrOptions["xAxis"]["labels"]["style"]["color"] = $strFontColor;
        $this->arrOptions["xAxis"]["title"]["style"]["color"] = $strFontColor;

        $this->arrOptions["yAxis"]["labels"]["style"]["color"] = $strFontColor;
        $this->arrOptions["yAxis"]["title"]["style"]["color"] = $strFontColor;

        $this->arrOptions["legend"]["itemStyle"]["color"] = $strFontColor;
    }

    /**
     * Sets the angle to be used for rendering the x-axis lables
     *
     * @param int $intXAxisAngle
     */
    public function setIntXAxisAngle($intXAxisAngle) {
        $this->arrOptions["xAxis"]["labels"]["rotation"] = $intXAxisAngle;
    }

    /**
     * @param \class_graph_highcharts_seriesdata[] $arrSeriesData
     */
    public function setArrSeriesData($arrSeriesData) {
        $this->arrSeriesData = $arrSeriesData;
    }

    /**
     * @return \class_graph_highcharts_seriesdata[]
     */
    public function getArrSeriesData() {
        return $this->arrSeriesData;
    }

    /**
     * @param $arrSeriesColors
     *
     * @return mixed
     */
    /**
     * @param $arrSeriesColors
     *
     * @return mixed
     */
    public function setArrSeriesColors($arrSeriesColors) {
        $this->arrSeriesColors = $arrSeriesColors;
        $this->arrOptions["colors"] = $arrSeriesColors;
    }

    /**
     * Sets the range for the xAxis.
     *
     * @param int $intMin
     * @param int $intMax
     */
    public function setXAxisRange($intMin, $intMax) {
        $this->arrOptions["xAxis"]["minRange"] = $intMin;
        $this->arrOptions["xAxis"]["maxRange"] = $intMax;
    }


    /**
     * Sets the range for the yAxis.
     *
     * @param int $intMin
     * @param int $intMax
     */
    public function setYAxisRange($intMin, $intMax) {
        $this->arrOptions["yAxis"]["minRange"] = $intMin;
        $this->arrOptions["yAxis"]["maxRange"] = $intMax;
    }


    /**
     * Method to render a horizontal bar chart
     *
     * @param bool $bitIsHorizontalBar
     */
    public function setBarHorizontal($bitIsHorizontalBar) {
        $this->bitIsHorizontalBar = $bitIsHorizontalBar;
    }


    /**
     * Hides the xAxis labels.
     * Also hide the grid line for the xAxis.
     *
     * @param bool $bitHideXAxis
     */
    public function setHideXAxis($bitHideXAxis) {
        $this->arrOptions["xAxis"]["labels"]["enabled"] = false;
        $this->arrOptions["xAxis"]["gridLineWidth"] = 0;
        $this->arrOptions["xAxis"]["minorGridLineWidth"] = 0;
//        $this->arrOptions["xAxis"]["tickOptions"]["showGridline"] = false;
        $this->bitXAxisLabelsInvisible = $bitHideXAxis;
    }


    /**
     * Hides the xAxis labels.
     * Also hide the grid line for the xAxis.
     *
     * @param bool $bitHideYAxis
     */
    public function setHideYAxis($bitHideYAxis) {
        $this->arrOptions["yAxis"]["labels"]["enabled"] = false;
        $this->arrOptions["yAxis"]["gridLineWidth"] = 0;
        $this->arrOptions["yAxis"]["minorGridLineWidth"] = 0;
        //$this->arrOptions["axes"]["yaxis"]["tickOptions"]["showGridline"] = false;
        $this->bitYAxisLabelsInvisible = $bitHideYAxis;
    }

}