<script setup lang="ts">
import { computed } from 'vue';
import Facebook from './Facebook.vue'
import Twitter from './Twitter.vue'
import Linkedin from './Linkedin.vue'
import Instagram from './Instagram.vue'
import Youtube from './Youtube.vue'
import Phone from './Phone.vue'
import Email from './Email.vue'
import Address from './Address.vue'
import Location from './Location.vue'
import Close from './Close.vue'
import ChevronUp from './ChevronUp.vue'
import ChevronDown from './ChevronDown.vue'
import Copy from './Copy.vue'
import Menu from './Menu.vue'
import Dashboard from './Dashboard.vue';
import Image from './Image.vue';
import People from './People.vue';
import ArrowForward from './ArrowForward.vue';
import ArrowBack from './ArrowBack.vue';
import Elements from './Elements.vue';
import Projects from './Projects.vue';
import Goals from './Goals.vue';
import News from './News.vue';
import Sections from './Sections.vue';
import CurvedArrowLeft from './CurvedArrowLeft.vue';
import CurvedArrowLeftDouble from './CurvedArrowLeftDouble.vue';
import CurvedArrowRight from './CurvedArrowRight.vue';
import CurvedArrowRightDouble from './CurvedArrowRightDouble.vue';
import Downloads from './Downloads.vue';
import Pdf from './Pdf.vue';
import Doc from './Doc.vue';
import Docx from './Docx.vue';
import Xlsx from './Xlsx.vue';
import Xls from './Xls.vue';
import Ppt from './Ppt.vue';
import CloudDownload from './CloudDownload.vue';
import Download from './Download.vue';
import EllipsisHorizontal from './EllipsisHorizontal.vue';
import { useHeroIcon } from '@/Composables/useHeroIcon';
import Zip from './Zip.vue';

const props = defineProps({ type: String })

const icon = computed(() => {
    if (props.type == 'facebook') {
        return Facebook;
    } else if (props.type == 'twitter') {
        return Twitter;
    } else if (props.type == 'linkedin') {
        return Linkedin;
    } else if (props.type == 'instagram') {
        return Instagram;
    } else if (props.type == 'youtube') {
        return Youtube;
    } else if (props.type == 'phone') {
        return Phone;
    } else if (props.type == 'email') {
        return Email;
    } else if (props.type == 'address') {
        return Address;
    } else if (props.type == 'location') {
        return Location;
    } else if (props.type == 'close') {
        return Close;
    } else if (props.type == 'chevron-up') {
        return ChevronUp;
    } else if (props.type == 'chevron-down') {
        return ChevronDown;
    } else if (props.type == 'copy') {
        return Copy;
    } else if (props.type == 'menu') {
        return Menu;
    } else if (props.type == 'dashboard') {
        return Dashboard;
    } else if (props.type == 'image') {
        return Image;
    } else if (props.type == 'people') {
        return People;
    } else if (props.type == 'arrow-forward') {
        return ArrowForward;
    } else if (props.type == 'arrow-back') {
        return ArrowBack;
    } else if (props.type == 'elements') {
        return Elements;
    } else if (props.type == 'projects') {
        return Projects;
    } else if (props.type == 'goals') {
        return Goals;
    } else if (props.type == 'news') {
        return News;
    } else if (props.type == 'sections') {
        return Sections;
    } else if (props.type == 'curved-arrow-left') {
        return CurvedArrowLeft;
    } else if (props.type == 'curved-arrow-left-double') {
        return CurvedArrowLeftDouble;
    } else if (props.type == 'curved-arrow-right') {
        return CurvedArrowRight;
    } else if (props.type == 'curved-arrow-right-double') {
        return CurvedArrowRightDouble;
    } else if (props.type == 'downloads') {
        return Downloads;
    } else if (props.type == 'pdf') {
        return Pdf;
    } else if (props.type == 'doc') {
        return Doc;
    } else if (props.type == 'docx') {
        return Docx;
    } else if (props.type == 'xls') {
        return Xls;
    } else if (props.type == 'xlsx') {
        return Xlsx;
    } else if (props.type == 'ppt') {
        return Ppt;
    } else if (props.type == 'pptx') {
        return Ppt;
    } else if (props.type == 'zip') {
        return Zip;
    } else if (props.type == 'tar') {
        return Zip;
    } else if (props.type == 'gz') {
        return Zip;
    } else if (props.type == 'tar.gz') {
        return Zip;
    } else if (props.type == 'tgz') {
        return Zip;
    } else if (props.type == 'cloud-download') {
        return CloudDownload;
    } else if (props.type == 'download') {
        return Download;
    } else if (props.type == 'ellipsis-horizontal') {
        return EllipsisHorizontal;
    }

    return useHeroIcon(props.type || '') ?? 'span';
})
</script>

<template>
    <component :is="icon" />
</template>
