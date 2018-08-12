import Vue from 'vue';
import './js/public-path';  // Must be first
import routes from './components/route';
import './js';

Vue.config.productionTip = false;

if (process.env.NODE_ENV === 'development') {
    const langs = [
        { lang: 'en', load: () => import('../../lang/en/front-end') },
        { lang: 'zh_CN', load: () => import('../../lang/zh_CN/front-end') },
    ];
    setTimeout(langs.find(({ lang }) => lang === blessing.locale).load, 0);
}

const route = routes.find(route => route.path === blessing.route);
if (route) {
    new Vue({
        el: route.el,
        render: h => h(route.component)
    });
}