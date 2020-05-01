// pages/play/play.js
//获取应用实例
const app = getApp()
var util= require('../../utils/util.js')
var video = null;
Component({
  /**
   * 组件的属性列表
   */
  properties: {

  },

  /**
   * 组件的初始数据
   */
  data: {
    vid:"",
    video_url:""
  },

  /**
   * 组件的方法列表
   */
  methods: {
    onLoad: function (options) {
      this.data.vid = options.vid;
      this.info(options.vid)
      video = wx.createVideoContext('video')
    },
    play:function(e)
    {
      // if(!app.globalData.isLogin) wx.reLaunch({
      //   url: '/pages/login/login',
      // })
      if(!app.globalData.isLogin) {
        wx.navigateTo({
          url: '/pages/login/login',
        })
      }else
      {
        this.info(this.data.vid);
      }
    },

    getUrl:function(vid,xid="")
    {
      var that = this;
      util.request(app.globalData.api_url+"/video/url","POST",{vid:vid,xid:xid},true).then((res) => {
        if(res.status == 200)
        {
          that.setData({
            video_url:res.data.url
          });
        }else
        {
          wx.hideLoading()
          wx.showModal({
            title:"获取视频地址失败",
            content:"没有获取到视频数据，请刷新重试，点击取消返回上一页面。",
            cancelText:"取消",
            confirmText:"刷新",
            success(res){
              if (res.confirm) {
                that.onLoad({vid:vid});
              } else if (res.cancel) {
                wx.navigateBack({
                  delta: 1,
                })
              }
              
            }
          });
        }
      })
      .catch((res)=>{
        console.log(res);
      })
    },
    info:function(vid)
    {
      var that = this;
      util.request(app.globalData.api_url+"/video/info","POST",{vid:vid},true).then((res) => {
        console.log(res);
        if(res.status == 200)
        {
          that.setData({
            title:res.data.title,
            tag:res.data.tag,
            actor:res.data.actor,
            desc:res.data.desc
          });
          if(res.data.type == "movie") this.getUrl(res.data.vid);
          else if(res.data.type == "tv" && res.data.list.length > 0) this.getUrl(res.data.vid,res.data.list[0]['xid']);
        }else
        {
          wx.hideLoading()
          wx.showModal({
            title:"获取视频地址失败",
            content:"没有获取到视频数据，请刷新重试，点击取消返回上一页面。",
            cancelText:"取消",
            confirmText:"刷新",
            success(res){
              if (res.confirm) {
                that.onLoad({vid:vid});
              } else if (res.cancel) {
                wx.navigateBack({
                  delta: 1,
                })
              }
              
            }
          });
        }
      })
      .catch((res)=>{
        console.log(res);
      })
    }
  }
})
