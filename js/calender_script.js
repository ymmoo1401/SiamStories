
/* ======================================
   1. แบนเนอร์เลื่อนอัตโนมัติ (จัดการโดย js_index.js)
====================================== */
const heroTrack = document.getElementById("hero-track");
if (heroTrack) {
    const heroImages = [
        "Img/east-img/เหนือพื้นหลัง.jpg",
        "Img/central-img/กลางพื้นหลัง.jpg",
        "Img/isan-img/อีสานพื้นหลัง.png",
        "Img/south-img/ใต้พื้นหลัง.jpg"
    ];
    heroImages.forEach(src => {
        const slide = document.createElement("div");
        slide.className = "hero-slide";
        slide.style.backgroundImage = `url("${src}")`;
        heroTrack.appendChild(slide);
    });
}

/* ======================================
   2. ข้อมูลภูมิภาค
====================================== */
const regions = {
    north: {
        name: "ภาคเหนือ",
        description: "เสน่ห์วัฒนธรรมล้านนา ประเพณีแห่งศรัทธา และวิถีชีวิตท่ามกลางขุนเขา"
    },
    isan: {
        name: "ภาคอีสาน",
        description: "สัมผัสงานบุญ ฮีตสิบสอง วิถีชุมชน และวัฒนธรรมที่สืบทอดจากรุ่นสู่รุ่น"
    },
    central: {
        name: "ภาคกลาง",
        description: "ดินแดนแห่งสายน้ำ อารยธรรม ศิลปวัฒนธรรม และประเพณีไทย"
    },
    south: {
        name: "ภาคใต้",
        description: "เรียนรู้ประเพณีแห่งศรัทธา ศิลปะพื้นบ้าน และวิถีชีวิตอันหลากหลาย"
    }
};

/* ======================================
   3. ข้อมูลประเพณีทั้ง 4 ภาค
====================================== */
const traditions = [

    /* ==================================
       ภาคเหนือ 20 ประเพณี
    ================================== */

    {
        region:"north",
        name:"ประเพณียี่เป็ง",
        province:"เชียงใหม่",
        months:[11],
        image:"Img/east-img/ประเพณียี่เป็ง.jpg",
        detail:"การจุดผางประทีปและประดับโคมไฟตามวัฒนธรรมล้านนา"
    },
    {
        region:"north",
        name:"ประเพณีปอยส่างลอง",
        province:"แม่ฮ่องสอน",
        months:[3,4],
        image:"Img/east-img/ประเพณีปอยส่างลอง.jpg",
        detail:"พิธีบรรพชาสามเณรตามวัฒนธรรมไทใหญ่ที่งดงาม"
    },
    {
        region:"north",
        name:"ประเพณีสรงน้ำพระธาตุ",
        province:"ภาคเหนือ",
        months:[4,5],
        image:"Img/east-img/ประเพณีสรงน้ำพระธาตุหริภุญชัย.jpg",
        detail:"ชาวพุทธร่วมสรงน้ำพระธาตุและทำบุญตามจารีตล้านนา"
    },
    {
        region:"north",
        name:"ประเพณีตานก๋วยสลาก",
        province:"เชียงใหม่และลำพูน",
        months:[9,10],
        image:"Img/east-img/ประเพณีตานก๋วยสลาก.jpg",
        detail:"ถวายเครื่องไทยธรรมโดยวิธีจับสลากและอุทิศบุญให้ผู้ล่วงลับ"
    },
    {
        region:"north",
        name:"ประเพณีปี๋ใหม่เมือง",
        province:"เชียงใหม่",
        months:[4],
        image:"Img/east-img/ประเพณีปี๋ใหม่เมือง.jpg",
        detail:"ฉลองปีใหม่ล้านนาด้วยการทำบุญ รดน้ำดำหัวผู้ใหญ่ และสืบสานประเพณีสงกรานต์"
    },
    {
        region:"north",
        name:"ประเพณีแห่ไม้ค้ำโพธิ์",
        province:"เชียงใหม่",
        months:[4],
        image:"Img/east-img/ประเพณีแห่ไม้ค้ำโพธิ์.jpg",
        detail:"ชาวบ้านนำไม้ค้ำไปถวายวัดเพื่อค้ำต้นโพธิ์ สื่อถึงการค้ำจุนพระพุทธศาสนา"
    },
    {
        region:"north",
        name:"ประเพณีอุ้มพระดำน้ำ",
        province:"เพชรบูรณ์",
        months:[9,10],
        image:"Img/east-img/ประเพณีอุ้มพระดำน้ำ.jpg",
        detail:"อัญเชิญพระพุทธมหาธรรมราชาลงสรงน้ำในแม่น้ำป่าสัก ตามความเชื่อเรื่องความอุดมสมบูรณ์"
    },
    {
        region:"north",
        name:"ประเพณีแข่งเรือยาว",
        province:"น่าน",
        months:[9,10],
        image:"Img/east-img/ประเพณีแข่งเรือยาว.jpg",
        detail:""
    },
    {
        region:"north",
        name:"ประเพณีฟ้อนผีมดผีเม็ง",
        province:"เชียงใหม่และลำพูน",
        months:[4,5,6],
        image:"Img/east-img/ประเพณีฟ้อนผีมดผีเม็ง.jpg",
        detail:"ประเพณีบูชาผีบรรพบุรุษของชาวล้านนา มีการฟ้อนรำและประกอบพิธีกรรม เพื่อแสดงความกตัญญูและขอความเป็นสิริมงคลแก่ครอบครัว"
    },
    {
        region:"north",
        name:"ประเพณีใส่ขันดอกอินทขิล",
        province:"เชียงใหม่",
        months:[5,6],
        image:"Img/east-img/ประเพณีใส่ขันดอกอินทขิล.jpg",
        detail:"ชาวเชียงใหม่ร่วมถวายดอกไม้และเครื่องสักการะแด่เสาอินทขิลหรือเสาหลักเมือง ตามความเชื่อเพื่อความเป็นสิริมงคล"
    },
    {
        region:"north",
        name:"ประเพณีตานตุง",
        province:"เชียงใหม่และลำพูน",
        months:[4],
        image:"Img/east-img/ประเพณีตานตุง.jpg",
        detail:"ชาวล้านนานำตุงหรือธงผ้าพื้นเมืองไปถวายวัด เพื่อเป็นพุทธบูชาและอุทิศบุญกุศลให้ผู้ล่วงลับ"
    },
    {
        region:"north",
        name:"ประเพณีปอยหลวง",
        province:"เชียงใหม่และลำพูน",
        months:[2,3,4],
        image:"Img/east-img/ประเพณีปอยหลวง.jpg",
        detail:"งานทำบุญเฉลิมฉลองเสนาสนะหรือสิ่งก่อสร้างภายในวัด มีการจัดขบวนแห่ครัวทานและร่วมกันทำบุญ"
    },
    {
        region:"north",
        name:"ประเพณีเลี้ยงผีปู่ย่า",
        province:"ลำพูนและเชียงใหม่",
        months:[4,5,6],
        image:"Img/east-img/ประเพณีเลี้ยงผีปู่ย่า.jpg",
        detail:"พิธีเซ่นไหว้ผีบรรพบุรุษของครอบครัวชาวล้านนา เพื่อแสดงความเคารพและสืบสานความผูกพันของเครือญาติ"
    },
    {
        region:"north",
        name:"ประเพณีแห่ครัวทาน",
        province:"เชียงใหม่และลำพูน",
        months:[2,3,4],
        image:"Img/east-img/ประเพณีแห่ครัวทาน.jpg",
        detail:"ชาวบ้านร่วมกันจัดเครื่องไทยทานและสิ่งของจำเป็นเป็นขบวนแห่ไปถวายวัดในโอกาสงานบุญ"
    },
    {
        region:"north",
        name:"ประเพณีตานข้าวใหม่",
        province:"ภาคเหนือ",
        months:[1,2],
        image:"Img/east-img/ประเพณีตานข้าวใหม่.jpg",
        detail:"ชาวบ้านนำข้าวที่เก็บเกี่ยวได้ใหม่ไปทำบุญถวายพระ เพื่อขอบคุณผลผลิตและแบ่งปันความอุดมสมบูรณ์"
    },
    {
        region:"north",
        name:"ประเพณีสืบชะตาเมือง",
        province:"เชียงใหม่",
        months:[4,5,6],
        image:"Img/east-img/ประเพณีสืบชะตาเมือง.jpg",
        detail:"พิธีกรรมล้านนาที่จัดขึ้นเพื่อความเป็นสิริมงคลของบ้านเมืองและชุมชน โดยมีการสวดเจริญพระพุทธมนต์"
    },
    {
        region:"north",
        name:"ประเพณีขึ้นธาตุเดือนเก้า",
        province:"ตาก",
        months:[5,6],
        image:"Img/east-img/ประเพณีขึ้นธาตุเดือนเก้า.jpg",
        detail:"งานนมัสการพระบรมธาตุบ้านตาก จังหวัดตาก ในช่วงเดือนเก้าตามปฏิทินล้านนา มีการทำบุญและเวียนเทียน"
    },
    {
        region:"north",
        name:"ประเพณีตานข้าวจี่",
        province:"ภาคเหนือ",
        months:[2,3],
        image:"Img/east-img/ประเพณีตานข้าวจี่.jpg",
        detail:"ชาวบ้านทำข้าวเหนียวปั้นย่างไฟไปถวายพระสงฆ์ในช่วงเทศกาลทำบุญ สะท้อนการแบ่งปันอาหารในชุมชน"
    },
    {
        region:"north",
        name:"ประเพณีจุดบอกไฟ",
        province:"ลำพูนและเชียงใหม่",
        months:[5,6],
        image:"Img/east-img/ประเพณีจุดบอกไฟ.jpg",
        detail:"การจุดบอกไฟหรือบั้งไฟตามแบบท้องถิ่นล้านนาในงานบุญบางพื้นที่ สื่อถึงความรื่นเริงและความสามัคคี"
    },
    {
        region:"north",
        name:"ประเพณีเลี้ยงผีขุนน้ำ",
        province:"เชียงใหม่และลำพูน",
        months:[5,6],
        image:"Img/east-img/ประเพณีเลี้ยงผีขุนน้ำ.jpg",
        detail:"ชุมชนทำพิธีบวงสรวงสิ่งศักดิ์สิทธิ์ที่ดูแลต้นน้ำ เพื่อแสดงความเคารพต่อธรรมชาติและร่วมกันอนุรักษ์แหล่งน้ำ"
    },

    
    // ========================================
    // ภาคอีสาน 20 ประเพณี
    // ========================================

    // ===== 5 ประเพณีเดิม (ไม่แก้ไข) =====

    {
        region:"isan",
        name:"ประเพณีบุญบั้งไฟ",
        province:"ยโสธร",
        months:[5,6],
        image:"Img/isan-img/ประเพณีบุญบั้งไฟ.jpg",
        emoji:"🚀",
        detail:"จัดขบวนแห่และจุดบั้งไฟตามความเชื่อเรื่องการขอฝน"
    },
    {
        region:"isan",
        name:"ประเพณีผีตาโขน",
        province:"เลย",
        months:[6,7],
        image:"Img/isan-img/ประเพณีผีตาโขน.jpg",
        emoji:"🎭",
        detail:"ขบวนแห่หน้ากากหลากสีในงานบุญหลวงอำเภอด่านซ้าย"
    },
    {
        region:"isan",
        name:"ประเพณีแห่เทียนพรรษา",
        province:"อุบลราชธานี",
        months:[7,8],
        image:"Img/isan-img/ประเพณีแห่เทียนพรรษา.jpg",
        emoji:"🕯️",
        detail:"ขบวนแห่ต้นเทียนแกะสลักและงานบุญเข้าพรรษา"
    },
    {
        region:"isan",
        name:"ประเพณีไหลเรือไฟ",
        province:"นครพนม",
        months:[9,10,11],
        image:"Img/isan-img/ประเพณีไหลเรือไฟ.jpg",
        emoji:"🚣",
        detail:"การประดับเรือไฟล่องแม่น้ำโขงในช่วงออกพรรษา"
    },
    {
        region:"isan",
        name:"ประเพณีบุญผะเหวด",
        province:"ร้อยเอ็ด",
        months:[3,4],
        image:"Img/isan-img/ประเพณีบุญผะเหวด.jpg",
        emoji:"📜",
        detail:"งานบุญเทศน์มหาชาติที่สะท้อนศรัทธาของชาวอีสาน"
    },

    // ===== เพิ่มใหม่ 15 ประเพณี =====

    // 6
    {
        region:"isan",
        name:"ประเพณีแห่ปราสาทผึ้ง",
        province:"สกลนคร",
        months:[10,11],
        image:"Img/isan-img/ประเพณีแห่ปราสาทผึ้ง.jpg",
        emoji:"🏯",
        detail:"ชาวบ้านร่วมกันสร้างปราสาทจากขี้ผึ้งและจัดขบวนแห่ถวายวัดในช่วงออกพรรษา เพื่อสืบสานศิลปวัฒนธรรมท้องถิ่น"
    },

    // 7
    {
        region:"isan",
        name:"ประเพณีบุญข้าวจี่",
        province:"ขอนแก่น",
        months:[2,3],
        image:"Img/isan-img/ประเพณีบุญข้าวจี่.jpg",
        emoji:"🍚",
        detail:"ชาวบ้านนำข้าวเหนียวปั้นเป็นก้อนย่างไฟและชุบไข่เพื่อนำไปถวายพระสงฆ์ เป็นการทำบุญตามฮีตสิบสอง"
    },

    // 8
    {
        region:"isan",
        name:"ประเพณีบุญคูนลาน",
        province:"มหาสารคาม",
        months:[1,2],
        image:"Img/isan-img/ประเพณีบุญคูนลาน.jpg",
        emoji:"🌾",
        detail:"พิธีทำบุญข้าวเปลือกหลังฤดูเก็บเกี่ยว เพื่อขอบคุณสิ่งศักดิ์สิทธิ์และขอให้ผลผลิตอุดมสมบูรณ์"
    },

    // 9
    {
        region:"isan",
        name:"ประเพณีบุญข้าวประดับดิน",
        province:"กาฬสินธุ์",
        months:[8,9],
        image:"Img/isan-img/ประเพณีบุญข้าวประดับดิน.jpg",
        emoji:"🍃",
        detail:"ชาวบ้านจัดอาหารและข้าวใส่ห่อใบตองเพื่อนำไปวางตามพื้นที่ต่าง ๆ เป็นการอุทิศส่วนกุศลให้ผู้ล่วงลับ"
    },

    // 10
    {
        region:"isan",
        name:"ประเพณีบุญข้าวสาก",
        province:"ร้อยเอ็ด",
        months:[9,10],
        image:"Img/isan-img/ประเพณีบุญข้าวสาก.jpg",
        emoji:"🥣",
        detail:"ประเพณีทำบุญถวายอาหารและเครื่องไทยทานแก่พระสงฆ์ พร้อมอุทิศส่วนกุศลให้บรรพบุรุษ"
    },

    // 11
    {
        region:"isan",
        name:"ประเพณีบุญซำฮะ",
        province:"อำนาจเจริญ",
        months:[6,7],
        image:"Img/isan-img/ประเพณีบุญซำฮะ.jpg",
        emoji:"💧",
        detail:"พิธีชำระล้างสิ่งไม่ดีและปัดเป่าเคราะห์ภัยออกจากชุมชน เพื่อความสงบสุขและความเป็นสิริมงคล"
    },

    // 12
    {
        region:"isan",
        name:"ประเพณีบุญเข้ากรรม",
        province:"นครราชสีมา",
        months:[12,1],
        image:"Img/isan-img/ประเพณีบุญเข้ากรรม.jpg",
        emoji:"🙏",
        detail:"ชาวบ้านร่วมทำบุญและถวายปัจจัยแด่พระสงฆ์ที่ปฏิบัติธรรมและเข้าปริวาสกรรมตามพระวินัย"
    },

    // 13
    {
        region:"isan",
        name:"ประเพณีบุญกฐิน",
        province:"บุรีรัมย์",
        months:[10,11],
        image:"Img/isan-img/ประเพณีบุญกฐิน.jpg",
        emoji:"🎁",
        detail:"พุทธศาสนิกชนร่วมกันถวายผ้ากฐินและเครื่องบริวารแด่พระสงฆ์หลังออกพรรษา เพื่อทำนุบำรุงพระพุทธศาสนา"
    },

    // 14
    {
        region:"isan",
        name:"ประเพณีบุญออกพรรษา",
        province:"หนองคาย",
        months:[10,11],
        image:"Img/isan-img/ประเพณีบุญออกพรรษา.jpg",
        emoji:"🪷",
        detail:"ชาวบ้านร่วมตักบาตร ทำบุญ และประกอบพิธีทางพระพุทธศาสนาเมื่อสิ้นสุดระยะเวลาจำพรรษา"
    },

    // 15
    {
        region:"isan",
        name:"ประเพณีบุญสงกรานต์อีสาน",
        province:"ชัยภูมิ",
        months:[4],
        image:"Img/isan-img/ประเพณีบุญสงกรานต์อีสาน.jpg",
        emoji:"💦",
        detail:"ชาวบ้านร่วมสรงน้ำพระ รดน้ำดำหัวผู้ใหญ่ และทำบุญต้อนรับปีใหม่ไทยตามวิถีวัฒนธรรมอีสาน"
    },

    // 16
    {
        region:"isan",
        name:"ประเพณีแห่นาคโหด",
        province:"ชัยภูมิ",
        months:[4,5],
        image:"Img/isan-img/ประเพณีแห่นาคโหด.jpg",
        emoji:"🎊",
        detail:"ชาวบ้านจัดขบวนแห่นาคอย่างสนุกสนาน โดยมีการโยกและเขย่าแคร่หามนาค เพื่อทดสอบความอดทนก่อนอุปสมบท"
    },

    // 17
    {
        region:"isan",
        name:"ประเพณีขึ้นเขาพนมรุ้ง",
        province:"บุรีรัมย์",
        months:[4],
        image:"Img/isan-img/ประเพณีขึ้นเขาพนมรุ้ง.jpg",
        emoji:"🏛️",
        detail:"งานสืบสานวัฒนธรรมขอมโบราณบริเวณปราสาทหินพนมรุ้ง มีขบวนแห่และการแสดงศิลปวัฒนธรรมท้องถิ่น"
    },

    // 18
    {
        region:"isan",
        name:"ประเพณีแห่ช้างบวชนาค",
        province:"สุรินทร์",
        months:[4,5],
        image:"Img/isan-img/ประเพณีแห่ช้างบวชนาค.jpg",
        emoji:"🐘",
        detail:"ชาวกูยจัดขบวนแห่นาคบนหลังช้างเพื่อประกอบพิธีบรรพชาอุปสมบท สะท้อนความผูกพันระหว่างคนกับช้าง"
    },

    // 19
    {
        region:"isan",
        name:"ประเพณีบุญเบิกฟ้า",
        province:"มหาสารคาม",
        months:[1,2],
        image:"Img/isan-img/ประเพณีบุญเบิกฟ้า.jpg",
        emoji:"🌤️",
        detail:"พิธีบูชาพระแม่โพสพและสิ่งศักดิ์สิทธิ์ตามความเชื่อของชาวนา เพื่อความอุดมสมบูรณ์และความเป็นสิริมงคล"
    },

    // 20
    {
        region:"isan",
        name:"ประเพณีบุญแจกข้าว",
        province:"อุดรธานี",
        months:[1,2,3,4,5,6,7,8,9,10,11,12],
        image:"Img/isan-img/ประเพณีบุญแจกข้าว.jpg",
        emoji:"🕊️",
        detail:"พิธีทำบุญอุทิศส่วนกุศลให้ผู้ล่วงลับ โดยญาติพี่น้องร่วมกันถวายภัตตาหารและเครื่องไทยทานแด่พระสงฆ์"
    },


    /* ==================================
       ภาคกลาง (ข้อมูลเดิม)
    ================================== */

   
    // ========================================
    // ภาคกลาง 20 ประเพณี
    // ========================================

    

    // ===== เพิ่มใหม่ 16 ประเพณี =====

    
    {
        region:"central",
        name:"ประเพณีตักบาตรดอกไม้",
        province:"สระบุรี",
        months:[7,8],
        image:"Img/central-img/ประเพณีตักบาตรดอกไม้.jpg",
        emoji:"🌸",
        detail:"ชาวบ้านนำดอกเข้าพรรษามาถวายพระสงฆ์ที่วัดพระพุทธบาทราชวรมหาวิหาร เพื่อบูชาพระรัตนตรัย"
    },

    // 6
    {
        region:"central",
        name:"ประเพณีรับบัว",
        province:"สมุทรปราการ",
        months:[9,10],
        image:"Img/central-img/ประเพณีรับบัว.jpg",
        emoji:"🪷",
        detail:"ชาวบางพลีร่วมกันโยนดอกบัวลงเรือที่อัญเชิญหลวงพ่อโต เป็นการแสดงความเคารพและศรัทธาในพระพุทธศาสนา"
    },

    // 7
    {
        region:"central",
        name:"ประเพณีกวนข้าวทิพย์",
        province:"พระนครศรีอยุธยา",
        months:[5,6],
        image:"Img/central-img/ประเพณีกวนข้าวทิพย์.jpg",
        emoji:"🍚",
        detail:"ชาวบ้านร่วมกันกวนข้าวทิพย์จากธัญพืชและวัตถุดิบมงคล เพื่อนำไปถวายพระสงฆ์และแจกจ่ายแก่ผู้ร่วมงาน"
    },

    // 8
    {
        region:"central",
        name:"ประเพณีทำขวัญข้าว",
        province:"สุพรรณบุรี",
        months:[11,12],
        image:"Img/central-img/ประเพณีทำขวัญข้าว.jpg",
        emoji:"🌾",
        detail:"พิธีบูชาพระแม่โพสพเพื่อแสดงความกตัญญูต่อข้าวและขอให้การเพาะปลูกมีความอุดมสมบูรณ์"
    },

    // 9
    {
        region:"central",
        name:"ประเพณีกำฟ้า",
        province:"นครสวรรค์",
        months:[1,2,3],
        image:"Img/central-img/ประเพณีกำฟ้า.jpg",
        emoji:"🌤️",
        detail:"ประเพณีของชาวไทยพวนที่หยุดทำงานและร่วมประกอบพิธีเคารพบูชาฟ้า เพื่อความเป็นสิริมงคลแก่ชุมชน"
    },

    // 10
    {
        region:"central",
        name:"ประเพณีอุ้มพระดำน้ำ",
        province:"เพชรบูรณ์",
        months:[9,10],
        image:"Img/central-img/ประเพณีอุ้มพระดำน้ำ.jpg",
        emoji:"🌊",
        detail:"พิธีอัญเชิญพระพุทธมหาธรรมราชาลงดำน้ำในแม่น้ำป่าสัก ตามความเชื่อเพื่อความเป็นสิริมงคลและความอุดมสมบูรณ์"
    },

    // 11
    {
        region:"central",
        name:"ประเพณีวิ่งควาย",
        province:"ชลบุรี",
        months:[10,11],
        image:"Img/central-img/ประเพณีวิ่งควาย.jpg",
        emoji:"🐃",
        detail:"การแข่งขันวิ่งควายและการตกแต่งควายอย่างสวยงาม เพื่ออนุรักษ์วิถีชีวิตเกษตรกรรมและความผูกพันระหว่างชาวนากับควาย"
    },

    // 12
    {
        region:"central",
        name:"ประเพณีเผาเทียนเล่นไฟ",
        province:"สุโขทัย",
        months:[10,11],
        image:"Img/central-img/ประเพณีเผาเทียนเล่นไฟ.jpg",
        emoji:"🕯️",
        detail:"งานเฉลิมฉลองที่นำเสนอการจุดประทีปและการแสดงแสงสีในบรรยากาศเมืองประวัติศาสตร์สุโขทัย"
    },

    // 13
    {
        region:"central",
        name:"ประเพณีตักบาตรน้ำผึ้ง",
        province:"สมุทรสาคร",
        months:[9,10],
        image:"Img/central-img/ประเพณีตักบาตรน้ำผึ้ง.jpg",
        emoji:"🍯",
        detail:"ชาวไทยเชื้อสายมอญนำน้ำผึ้งไปถวายพระสงฆ์ เพื่อสืบทอดความเชื่อทางพระพุทธศาสนาและสร้างความสามัคคีในชุมชน"
    },

    // 14
    {
        region:"central",
        name:"ประเพณีทอดผ้าป่ากลางน้ำ",
        province:"สมุทรปราการ",
        months:[10,11],
        image:"Img/central-img/ประเพณีทอดผ้าป่ากลางน้ำ.jpg",
        emoji:"⛵",
        detail:"ชุมชนชาวประมงร่วมกันจัดพิธีทอดผ้าป่าทางน้ำ เพื่อทำบุญและสืบสานวิถีชีวิตที่ผูกพันกับสายน้ำ"
    },

    // 15
    {
        region:"central",
        name:"ประเพณีแห่เทียนพรรษาทางน้ำ",
        province:"สุพรรณบุรี",
        months:[7,8],
        image:"Img/central-img/ประเพณีแห่เทียนพรรษาทางน้ำ.jpg",
        emoji:"🚤",
        detail:"ชาวบ้านจัดขบวนเรือแห่เทียนพรรษาไปถวายวัด สะท้อนวิถีชีวิตชุมชนริมน้ำและความศรัทธาในพระพุทธศาสนา"
    },

    // 16
    {
        region:"central",
        name:"ประเพณีสารทไทย",
        province:"นครนายก",
        months:[9,10],
        image:"Img/central-img/ประเพณีสารทไทย.jpg",
        emoji:"🥮",
        detail:"ชาวบ้านทำบุญถวายกระยาสารทและอาหารแก่พระสงฆ์ เพื่ออุทิศส่วนกุศลให้บรรพบุรุษและผู้ล่วงลับ"
    },

    // 17
    {
        region:"central",
        name:"ประเพณีตักบาตรพระร้อย",
        province:"ปทุมธานี",
        months:[10,11],
        image:"Img/central-img/ประเพณีตักบาตรพระร้อย.jpg",
        emoji:"🙏",
        detail:"ชาวไทยเชื้อสายมอญร่วมทำบุญตักบาตรพระสงฆ์จำนวนมากที่เดินทางมาทางเรือในช่วงเทศกาลออกพรรษา"
    },

    // 18
    {
        region:"central",
        name:"ประเพณีห่มผ้าองค์พระปฐมเจดีย์",
        province:"นครปฐม",
        months:[10,11,12],
        image:"Img/central-img/ประเพณีห่มผ้าองค์พระปฐมเจดีย์.jpg",
        emoji:"🛕",
        detail:"พุทธศาสนิกชนร่วมถวายผ้าห่มองค์พระปฐมเจดีย์ เพื่อแสดงความเคารพศรัทธาและสืบทอดประเพณีทางพระพุทธศาสนา"
    },

    // 19
    {
        region:"central",
        name:"ประเพณีแห่เจ้าพ่อพระยาแล",
        province:"ชัยภูมิ",
        months:[1],
        image:"Img/central-img/ประเพณีแห่เจ้าพ่อพระยาแล.jpg",
        emoji:"🎊",
        detail:"ชาวชัยภูมิร่วมประกอบพิธีบวงสรวงและจัดขบวนแห่เพื่อรำลึกถึงพระยาภักดีชุมพล ผู้ก่อตั้งเมืองชัยภูมิ"
    },

    // 20
    {
        region:"central",
        name:"ประเพณีแห่หลวงพ่อโสธร",
        province:"ฉะเชิงเทรา",
        months:[4,11],
        image:"Img/central-img/ประเพณีแห่หลวงพ่อโสธร.jpg",
        emoji:"🚩",
        detail:"ชาวบ้านร่วมขบวนแห่หลวงพ่อโสธรทั้งทางบกและทางน้ำ เพื่อสักการะพระพุทธรูปศักดิ์สิทธิ์คู่บ้านคู่เมือง"
    },

    {
        region:"central",
        name:"ประเพณีแห่ธงตะขาบ",
        province:"สมุทรปราการ",
        months:[4],
        image:"Img/central-img/ประเพณีแห่ธงตะขาบ.jpg",
        emoji:"🚩",
        detail:"ชาวไทยเชื้อสายมอญร่วมจัดขบวนแห่ธงตะขาบไปถวายวัดในเทศกาลสงกรานต์ เพื่อสืบสานวัฒนธรรมและความศรัทธาของชุมชน"
    },

    {
        region:"central",
        name:"ประเพณีทิ้งกระจาด",
        province:"พระนครศรีอยุธยา",
        months:[8,9],
        image:"Img/central-img/ประเพณีทิ้งกระจาด.jpg",
        emoji:"🚩",
        detail:"ประเพณีทำบุญแจกสิ่งของและอาหารแก่ผู้ยากไร้ เพื่ออุทิศส่วนกุศลและช่วยเหลือผู้ขาดแคลน สะท้อนความเมตตาและการแบ่งปันในชุมชน"
    },

    {
        region:"central",
        name:"ประเพณีเทศน์มหาชาติ",
        province:"สมุทรปราการ",
        months:[10,11],
        image:"Img/central-img/ประเพณีเทศน์มหาชาติ.jpg",
        emoji:"🚩",
        detail:"พุทธศาสนิกชนร่วมฟังเทศน์มหาชาติเรื่องพระเวสสันดรชาดก เพื่อสืบทอดพระพุทธศาสนาและปลูกฝังคุณธรรมเรื่องการให้ทาน"
    },

    {
        region:"central",
        name:"ประเพณีแห่พระศรีอาริย์",
        province:"อ่างทอง",
        months:[10],
        image:"Img/central-img/ประเพณีแห่พระศรีอาริย์.jpg",
        emoji:"🚩",
        detail:"พุทธศาสนิกชนร่วมทำบุญตักบาตรเนื่องในวันออกพรรษา เพื่อระลึกถึงพุทธประวัติเมื่อพระพุทธเจ้าเสด็จกลับจากสวรรค์ชั้นดาวดึงส์"
    },
    
    
    /* ==================================
       ภาคใต้ (ข้อมูลเดิม)
    ================================== */

    {
        region:"south",
        name:"ประเพณีชักพระ",
        province:"สุราษฎร์ธานี",
        months:[9,10,11],
        image:"Img/south-img/ประเพณีชักพระ.jpg",
        emoji:"⛩️",
        detail:"ขบวนแห่พระพุทธรูปในช่วงเทศกาลออกพรรษา"
    },
    {
        region:"south",
        name:"ประเพณีสารทเดือนสิบ",
        province:"นครศรีธรรมราช",
        months:[9,10],
        image:"Img/south-img/ประเพณีสารทเดือนสิบ.jpg",
        emoji:"🌼",
        detail:"งานทำบุญอุทิศส่วนกุศลแก่บรรพบุรุษและญาติผู้ล่วงลับ"
    },
    {
        region:"south",
        name:"ประเพณีแห่ผ้าขึ้นธาตุ",
        province:"นครศรีธรรมราช",
        months:[2,3],
        image:"Img/south-img/ประเพณีแห่ผ้าขึ้นธาตุ.jpg",
        emoji:"🛕",
        detail:"นำผ้าพระบฏไปห่มองค์พระบรมธาตุเจดีย์"
    },
    {
        region:"south",
        name:"ประเพณีลอยเรือชาวเล",
        province:"สตูลและภูเก็ต",
        months:[5,6,10,11],
        image:"Img/south-img/ประเพณีลอยเรือชาวเล.jpg",
        emoji:"⛵",
        detail:"พิธีกรรมของชุมชนชาวเลเพื่อความเป็นสิริมงคล"
    },

    /* ==================================
       เพิ่มใหม่ 16 ประเพณี
    ================================== */

    // 5
    {
        region:"south",
        name:"ประเพณีโนราโรงครู",
        province:"พัทลุงและสงขลา",
        months:[4,5,6,7,8,9],
        image:"Img/south-img/ประเพณีโนราโรงครู.jpg",
        emoji:"🎭",
        detail:"พิธีไหว้ครูและบูชาบรรพบุรุษของคณะโนรา มีการรำประกอบดนตรีพื้นบ้านเพื่อแสดงความกตัญญูต่อครูหมอโนรา"
    },

    // 6
    {
        region:"south",
        name:"ประเพณีแข่งโพน",
        province:"พัทลุง",
        months:[9,10,11],
        image:"Img/south-img/ประเพณีแข่งโพน.jpg",
        emoji:"🥁",
        detail:"การแข่งขันตีโพนหรือกลองพื้นบ้านเพื่อประชันเสียงและความกังวาน เป็นวัฒนธรรมที่สืบทอดควบคู่กับงานชักพระ"
    },

    // 7
    {
        region:"south",
        name:"ประเพณีแห่นางดาน",
        province:"นครศรีธรรมราช",
        months:[4],
        image:"Img/south-img/ประเพณีแห่นางดาน.jpg",
        emoji:"🌺",
        detail:"พิธีกรรมโบราณที่เกี่ยวข้องกับความเชื่อพราหมณ์ มีการแห่แผ่นไม้แกะสลักรูปเทพเจ้า เพื่อสืบสานมรดกวัฒนธรรมเมืองนคร"
    },

    // 8
    {
        region:"south",
        name:"ประเพณีลาซัง",
        province:"ปัตตานีและนราธิวาส",
        months:[2,3,4],
        image:"Img/south-img/ประเพณีลาซัง.jpg",
        emoji:"🌾",
        detail:"พิธีกรรมหลังฤดูเก็บเกี่ยวข้าวของชุมชนชาวนา เพื่อแสดงความเคารพต่อแม่โพสพและขอความอุดมสมบูรณ์ในการเพาะปลูก"
    },

    // 9
    {
        region:"south",
        name:"ประเพณีกวนอาซูรอ",
        province:"ปัตตานี ยะลา และนราธิวาส",
        months:[1,2,3,4,5,6,7,8,9,10,11,12],
        image:"Img/south-img/ประเพณีกวนอาซูรอ.jpg",
        emoji:"🥣",
        detail:"ชาวไทยมุสลิมร่วมกันกวนขนมอาซูรอในเดือนมุฮัรรอมตามปฏิทินอิสลาม เพื่อสร้างความสามัคคีและแบ่งปันอาหารในชุมชน"
    },

    // 10
    {
        region:"south",
        name:"ประเพณีแห่นก",
        province:"ปัตตานี ยะลา และนราธิวาส",
        months:[1,2,3,4,5,6,7,8,9,10,11,12],
        image:"Img/south-img/ประเพณีแห่นก.jpg",
        emoji:"🦚",
        detail:"ขบวนแห่นกจำลองขนาดใหญ่ที่ประดับตกแต่งอย่างสวยงาม สะท้อนศิลปวัฒนธรรมมลายูท้องถิ่นและความร่วมมือของชุมชน"
    },

    // 11
    {
        region:"south",
        name:"ประเพณีถือศีลกินผัก",
        province:"ภูเก็ต",
        months:[9,10,11],
        image:"Img/south-img/ประเพณีถือศีลกินผัก.jpg",
        emoji:"🥬",
        detail:"ชาวไทยเชื้อสายจีนร่วมถือศีลและรับประทานอาหารเจเป็นเวลาเก้าวัน พร้อมประกอบพิธีกรรมตามความเชื่อของศาลเจ้า"
    },

    // 12
    {
        region:"south",
        name:"ประเพณีแห่พระลุยไฟ",
        province:"ยะลา",
        months:[2,3],
        image:"Img/south-img/ประเพณีแห่พระลุยไฟ.jpg",
        emoji:"🔥",
        detail:"พิธีแห่พระและลุยไฟตามความเชื่อของชุมชนชาวไทยเชื้อสายจีน เพื่อแสดงความศรัทธาต่อสิ่งศักดิ์สิทธิ์และขอความเป็นสิริมงคล"
    },

    // 13
    {
        region:"south",
        name:"ประเพณีแห่เจ้าแม่ลิ้มกอเหนี่ยว",
        province:"ปัตตานี",
        months:[2,3],
        image:"Img/south-img/ประเพณีแห่เจ้าแม่ลิ้มกอเหนี่ยว.jpg",
        emoji:"🏮",
        detail:"ชาวไทยเชื้อสายจีนจัดขบวนแห่องค์เจ้าแม่ลิ้มกอเหนี่ยวพร้อมประกอบพิธีกรรม เพื่อแสดงความเคารพต่อสิ่งศักดิ์สิทธิ์คู่เมืองปัตตานี"
    },

    // 14
    {
        region:"south",
        name:"ประเพณีฮารีรายออีฎิ้ลฟิตรี",
        province:"ปัตตานี ยะลา และนราธิวาส",
        months:[1,2,3,4,5,6,7,8,9,10,11,12],
        image:"Img/south-img/ประเพณีฮารีรายออีฎิ้ลฟิตรี.jpg",
        emoji:"🌙",
        detail:"ชาวไทยมุสลิมเฉลิมฉลองหลังสิ้นสุดเดือนรอมฎอน มีการละหมาดร่วมกัน เยี่ยมญาติ และแบ่งปันอาหารเพื่อสร้างความสัมพันธ์ในครอบครัว"
    },

    // 15
    {
        region:"south",
        name:"ประเพณีฮารีรายออีฎิ้ลอัฎฮา",
        province:"ปัตตานี ยะลา และนราธิวาส",
        months:[1,2,3,4,5,6,7,8,9,10,11,12],
        image:"Img/south-img/ประเพณีฮารีรายออีฎิ้ลอัฎฮา.jpg",
        emoji:"🐑",
        detail:"เทศกาลสำคัญของชาวมุสลิม มีการละหมาดและประกอบพิธีกุรบานเพื่อแสดงความศรัทธาและแบ่งปันอาหารแก่ผู้ยากไร้"
    },

    // 16
    {
        region:"south",
        name:"ประเพณีเมาลิด",
        province:"สงขลาและปัตตานี",
        months:[1,2,3,4,5,6,7,8,9,10,11,12],
        image:"Img/south-img/ประเพณีเมาลิด.jpg",
        emoji:"🕌",
        detail:"กิจกรรมรำลึกถึงวันประสูติของศาสดามุฮัมมัด มีการอ่านประวัติศาสดาและจัดกิจกรรมทางศาสนาร่วมกันในชุมชน"
    },

    // 17
    {
        region:"south",
        name:"ประเพณีมาโซยาวี",
        province:"นราธิวาส",
        months:[1,2,3,4,5,6,7,8,9,10,11,12],
        image:"Img/south-img/ประเพณีมาโซยาวี.jpg",
        emoji:"🎉",
        detail:"พิธีเข้าสุนัตของเด็กชายในชุมชนมลายูมุสลิม มีการจัดงานเลี้ยงและกิจกรรมร่วมกันของญาติพี่น้องตามวิถีวัฒนธรรมท้องถิ่น"
    },

    // 18
    {
        region:"south",
        name:"ประเพณีตักบาตรธูปเทียน",
        province:"นครศรีธรรมราช",
        months:[7,8],
        image:"Img/south-img/ประเพณีตักบาตรธูปเทียน.jpg",
        emoji:"🕯️",
        detail:"พุทธศาสนิกชนร่วมถวายธูปเทียนและเครื่องสักการะแก่พระสงฆ์ในช่วงเข้าพรรษา เพื่อสืบทอดประเพณีทางพระพุทธศาสนา"
    },

    // 19
    {
        region:"south",
        name:"ประเพณีวันว่าง",
        province:"นครศรีธรรมราชและสงขลา",
        months:[4],
        image:"Img/south-img/ประเพณีวันว่าง.jpg",
        emoji:"💦",
        detail:"ประเพณีช่วงปีใหม่ของชาวภาคใต้ มีการหยุดพักจากการทำงาน ทำบุญ สรงน้ำพระ และขอพรผู้ใหญ่เพื่อความเป็นสิริมงคล"
    },

    // 20
    {
        region:"south",
        name:"ประเพณีไหว้ครูหนังตะลุง",
        province:"พัทลุงและนครศรีธรรมราช",
        months:[1,2,3,4,5,6,7,8,9,10,11,12],
        image:"Img/south-img/ประเพณีไหว้ครูหนังตะลุง.jpg",
        emoji:"🎬",
        detail:"พิธีบูชาครูและครูบาอาจารย์ผู้ถ่ายทอดศิลปะหนังตะลุง เพื่อแสดงความกตัญญูและสืบสานศิลปะการแสดงพื้นบ้านภาคใต้"
    },

];

/* ======================================
   4. ชื่อเดือน
====================================== */
const monthNames = [
    "ทั้งหมด","ม.ค.","ก.พ.","มี.ค.","เม.ย.","พ.ค.",
    "มิ.ย.","ก.ค.","ส.ค.","ก.ย.","ต.ค.","พ.ย.","ธ.ค."
];

let selectedRegion = null;
let selectedMonth = 0;

const monthContainer = document.getElementById("month-buttons");
const eventsContainer = document.getElementById("events");
const searchInput = document.getElementById("search-input");

/* ======================================
   5. สร้างปุ่มเลือกเดือน
====================================== */
monthNames.forEach((name, index) => {
    const button = document.createElement("button");

    button.textContent = name;
    button.dataset.month = index;

    button.addEventListener("click", () => {
        selectedMonth = index;
        render();
    });

    monthContainer.appendChild(button);
});

/* ======================================
   6. ระบบเลือกภูมิภาค
====================================== */
function selectRegion(region) {
    selectedRegion = (selectedRegion === region) ? null : region;
    selectedMonth = 0;
    searchInput.value = "";
    render();
}

document.querySelectorAll(".region").forEach(path => {

    path.addEventListener("click", () => {
        selectRegion(path.dataset.region);
    });

    path.addEventListener("keydown", event => {
        if (event.key === "Enter" || event.key === " ") {
            event.preventDefault();
            selectRegion(path.dataset.region);
        }
    });

});

document.querySelectorAll("[data-select]").forEach(button => {

    button.addEventListener("click", () => {
        selectRegion(button.dataset.select);
    });

});

searchInput.addEventListener("input", render);

/* ======================================
   7. ระบบแสดงประเพณีพร้อมรูปภาพ
====================================== */
function render() {

    const regionInfo = document.querySelector(".region-info");
    if (regionInfo) {
        regionInfo.dataset.region = selectedRegion || "all";
    }

    document.getElementById("region-title").textContent =
        selectedRegion ? regions[selectedRegion].name : "ประเพณีทั่วไทย";

    document.getElementById("region-description").textContent =
        selectedRegion
        ? regions[selectedRegion].description
        : "เลือกภูมิภาคจากแผนที่เพื่อสำรวจประเพณี หรือดูรายการจากทั่วประเทศไทย";

    document.querySelectorAll(".region").forEach(path => {
        path.classList.toggle(
            "selected", path.dataset.region === selectedRegion
        );
    });

    document.querySelectorAll("[data-select]").forEach(button => {
        button.classList.toggle(
            "active", button.dataset.select === selectedRegion
        );
    });

    document.querySelectorAll("[data-month]").forEach(button => {
        button.classList.toggle(
            "active", Number(button.dataset.month) === selectedMonth
        );
    });

    const keyword = searchInput.value.trim().toLowerCase();

    const filtered = traditions.filter(event => {

        const correctRegion =
            !selectedRegion || event.region === selectedRegion;

        const correctMonth =
            selectedMonth === 0 || event.months.includes(selectedMonth);

        const correctSearch =
            (event.name + " " + event.province + " " + event.detail)
            .toLowerCase().includes(keyword);

        return correctRegion && correctMonth && correctSearch;
    });

    document.getElementById("event-count").textContent =
        filtered.length + " รายการ";

    eventsContainer.replaceChildren();

    if (filtered.length === 0) {
        const empty = document.createElement("div");
        empty.className = "no-events";
        empty.textContent = "ไม่พบประเพณีที่ตรงกับตัวกรอง";
        eventsContainer.appendChild(empty);
        return;
    }

    filtered.forEach(event => {

        const card = document.createElement("article");
        card.className = "event-card";

        const icon = document.createElement("div");
        icon.className = "event-emoji";

        // แสดงรูปภาพของประเพณี
        // ถ้าไม่มีรูปภาพจะแสดง Emoji แทน
        if (event.image) {
            const img = document.createElement("img");

            img.src = event.image;
            img.alt = event.name;
            img.loading = "lazy";

            img.onerror = () => {
                img.remove();
                icon.textContent = event.emoji || "🌸";
            };

            icon.appendChild(img);
        } else {
            icon.textContent = event.emoji || "🌸";
        }

        const content = document.createElement("div");

        const title = document.createElement("h4");
        title.textContent = event.name;

        const description = document.createElement("p");
        description.textContent = event.detail;

        const meta = document.createElement("div");
        meta.className = "event-meta";

        const monthsFormatted = event.months.map(m => monthNames[m]).join(", ");

        meta.innerHTML = `
            <span class="meta-item meta-location">
                <svg class="meta-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"></path>
                    <circle cx="12" cy="9" r="2.5"></circle>
                </svg>
                <span>${event.province}</span>
            </span>
            <span class="meta-divider" aria-hidden="true">·</span>
            <span class="meta-item meta-date">
                <svg class="meta-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <rect x="3" y="4" width="18" height="18" rx="3"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                    <circle cx="8" cy="14" r="1" fill="currentColor"></circle>
                    <circle cx="12" cy="14" r="1" fill="currentColor"></circle>
                    <circle cx="16" cy="14" r="1" fill="currentColor"></circle>
                    <circle cx="8" cy="18" r="1" fill="currentColor"></circle>
                    <circle cx="12" cy="18" r="1" fill="currentColor"></circle>
                </svg>
                <span>${monthsFormatted}</span>
            </span>
        `;

        content.append(title, description, meta);

        card.append(icon, content);

        eventsContainer.appendChild(card);
    });
}

/* ======================================
   8. เมนูมือถือ
====================================== */
document.getElementById("menu-toggle").addEventListener("click", () => {
    document.getElementById("main-menu").classList.toggle("open");
});

/* ======================================
   9. เริ่มระบบ
====================================== */
render();
