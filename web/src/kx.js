import echarts from 'echarts'
import i18n from './i18n'

// K线图
export default {
  chart: null,
  init(ref) {
    this.chart = echarts.init(ref)
    this.chart.setOption({
      tooltip: {
        trigger: 'axis',
        formatter: function (params) {
          let res = i18n.t('message.time') + ' : ' + (new Date(params[0].name)).format();
          res += '<br/>  ' + i18n.t('trade.open') + ' : ' + params[0].value[1] + '  ' + i18n.t('trade.high') + ' : ' + params[0].value[4];
          res += '<br/>  ' + i18n.t('trade.close') + ' : ' + params[0].value[2] + '  ' + i18n.t('trade.low') + ' : ' + params[0].value[3];
          return res;
        }
      },
      xAxis: {
        scale: true,
        type: 'time',
        splitLine: {
          show: false
        },
        axisLine: {
          show: false,
          lineStyle: {
            color: '#e3e3e3'
          }
        },
        axisLabel: {
          color: '#79878F'
        }
      },
      yAxis: {
        scale: true,
        minInterval: 0.0004,
        maxInterval: 0.001,
        splitLine: {
          lineStyle: {
            color: '#e3e3e3'
          }
        },
        axisLine: {
          lineStyle: {
            color: '#e3e3e3'
          }
        },
        axisLabel: {
          color: '#79878F'
        }
      },
      grid: {
        x: 50,
        x2: 14,
        y: 10,
        y2: 20
      },
      series: [{
        name: 'rates',
        radius: '100%',
        type: 'k',
        data: [],
        barWidth: 8,
        itemStyle: {
          color: '#FD4322',
          color0: '#2F97E5'
        },
        emphasis: {
          itemStyle: {
            color: '#18B387',
            borderColor: '#20323E'
          }
        }
      }]
    }, true);
  },
  setData(datas) {
    this.chart.setOption({
      series: [{
        name: 'rates',
        data: datas
      }]
    })
  }
}