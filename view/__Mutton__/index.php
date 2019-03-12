<!DOCTYPE HTML>
<html>
<head>
    <title>Mutton Portal</title>
    <meta name="viewport" content="width=device-width,initial-scale=1,minimum-scale=1,maximum-scale=1,user-scalable=no">
    <link rel="stylesheet" href="<?php echo HTTP_STC ?>__Mutton__/index.css?7">
    <script src="https://cdn.jsdelivr.net/npm/vue@2.5.17/dist/vue.min.js"></script>
    <script>
        var tab = <?php echo $hasConfig ? '0' : '3' ?>;
        var HTTP_BASE = '<?php echo HTTP_BASE ?>';
    </script>
    <script src="<?php echo HTTP_STC ?>__Mutton__/index.js?7"></script>
</head>
<body>
<div id="vue">
    <div class="window">
        <div class="window__in">
            <div class="window__title">Mutton Portal</div>
            <div class="window__panel">
                <div class="title">Mutton Portal</div>
                <div class="tab">
                    <div class="tab__top">
                        <div class="tab__top__item" :class="{'selected': tab == 0}" @click="tab = 0"><div class="tab__top__item__in">Password</div></div>
                        <div class="tab__top__item" :class="{'selected': tab == 1}" @click="tab = 1"><div class="tab__top__item__in">Check</div></div>
                        <div class="tab__top__item" :class="{'selected': tab == 2}" @click="tab = 2"><div class="tab__top__item__in">System</div></div>
                        <div class="tab__top__item" :class="{'selected': tab == 3}" @click="tab = 3"><div class="tab__top__item__in">Config</div></div>
                    </div>
                    <div class="tab__panel">
                        <!-- Password -->
                        <div class="tab__panel__item" v-if="tab == 0">
                            <div style="padding-bottom: 10px;">Please enter your password:</div>
                            <div class="textbox"><input class="textbox__in" type="password" v-model="password"></div>
                            <div style="padding-top: 10px;">When the input is complete, you can use other features.</div>
                        </div>
                        <!-- Check -->
                        <div class="tab__panel__item" v-else-if="tab == 1">
                            <div style="padding-bottom: 10px;">Please enter the code:</div>
                            <div class="textbox"><textarea class="textbox__in" rows="8" v-model="code" style="resize: vertical;"></textarea></div>
                            <div style="text-align: center; margin-top: 10px;"><mu-button @click.native="check()">Check</mu-button><mu-button @click.native="check(true)" style="margin-left: 10px;">Strict check</mu-button><mu-button @click.native="check(true, true)" style="margin-left: 10px;">Full check</mu-button></div>
                            <div style="margin-top: 10px;">Mismatch file list:</div>
                            <mu-list :list="list"></mu-list>
                        </div>
                        <!-- System -->
                        <div class="tab__panel__item" v-else-if="tab == 2">
                            <div style="display: flex; flex-direction: column; align-items: center;">
                                <div>
                                    Current version: <?php echo VER ?><br>
                                    Latest versions: {{latestVer}}<br>
                                </div>
                                <mu-button @click.native="getLatestVer()" style="margin-top: 10px;">Get latest versions</mu-button>
                            </div>
                            <mu-line></mu-line>
                            <div style="display: flex; flex-direction: column; align-items: center;">
                                <div>Build a ".mblob" file.</div>
                                <mu-button @click.native="build()" style="margin-top: 10px;">Build</mu-button>
                            </div>
                        </div>
                        <!-- Config -->
                        <div class="tab__panel__item" v-else-if="tab == 3">
                            <div style="padding-bottom: 10px;">Please place the following on "etc/__mutton__.php" to use this portal.</div>
                            <div class="textbox"><textarea class="textbox__in" rows="10" readonly v-model="configTxt" style="resize: none;"></textarea></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- alert -->
    <div v-if="alert!=''" class="alert">
        <div class="window">
            <div class="window__in">
                <div class="window__title">Alert</div>
                <div class="window__panel" style="min-width: 250px;">{{alert}}</div>
                <div class="window__controls">
                    <mu-button @click.native="alert=''">Ok</mu-button>
                </div>
            </div>
        </div>
    </div>
    <!-- mask -->
    <div v-if="mask" class="mask"></div>
</div>
</body>
</html>