//   (c) 2013-2015 by Kajona, www.kajona.de
//       Published under the GNU LGPL v2.1, see /system/licence_lgpl.txt
//       $Id$

if (!KAJONA) {
    alert('load kajona.js before!');
}


KAJONA.admin.highchartsHelper = {

    arrChartObjects: [],//container for all chart objects

    /**
     *
     * @param strChartId - id of the chart
     */
    objChartWrapper : function (strChartId, objChartOptions) {
        this.strChartId = strChartId;
        this.objChartOptions = objChartOptions;

        this.objChart = null;//the actual jqPlot object

        /**
         * Called after the chart was plotted
         */
        this.postPlot = function() {
            //postPlot
        };

        /**
         * Plots the chart
         */
        this.plot = function () {
            this.objChart = $('#' + this.strChartId).highcharts(this.objChartOptions);
        };

        /**
         * Renders and plot the charts
         */
        this.render = function () {
            this.plot();
        };

        KAJONA.admin.highchartsHelper.arrChartObjects[this.strChartId] = this;
    }
}