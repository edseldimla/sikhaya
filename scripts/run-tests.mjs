// A fresh isolated copy makes the integration suite safe to repeat.
// Uses Node and PHP already installed on the developer's computer.
import fs from 'node:fs';
import path from 'node:path';
import net from 'node:net';
import { fileURLToPath } from 'node:url';
import { spawn, spawnSync } from 'node:child_process';

const root=path.resolve(path.dirname(fileURLToPath(import.meta.url)),'..');
const candidates=process.env.SIKHAYA_PHP
  ? [process.env.SIKHAYA_PHP]
  : ['php',...(process.platform==='win32'?['C:\\xampp\\php\\php.exe']:[])];
const php=candidates.find(executable=>spawnSync(executable,['--version'],{stdio:'ignore',windowsHide:true}).status===0);
if(!php){console.error('PHP was not found. Set SIKHAYA_PHP to your PHP executable path.');process.exit(1);}

const testRoot=path.join(root,'.local','test-runs');
fs.mkdirSync(testRoot,{recursive:true});
const workspace=fs.mkdtempSync(path.join(testRoot,'run-'));
for(const folder of ['app','database','public'])fs.cpSync(path.join(root,folder),path.join(workspace,folder),{recursive:true});
fs.mkdirSync(path.join(workspace,'scripts'));
fs.copyFileSync(path.join(root,'scripts','setup.php'),path.join(workspace,'scripts','setup.php'));
const testDatabase=path.join(workspace,'storage','database','test.sqlite');
const environment={...process.env,SIKHAYA_DB:testDatabase};

console.log('Creating a fresh test database. Your normal data is kept separate.');
const setup=spawnSync(php,['scripts/setup.php'],{cwd:workspace,env:environment,encoding:'utf8',windowsHide:true});
if(setup.status!==0){console.error(setup.stderr||setup.stdout||'Test setup failed.');process.exit(1);}

const port=await new Promise((resolve,reject)=>{
  const probe=net.createServer();
  probe.once('error',reject);
  probe.listen(0,'127.0.0.1',()=>{
    const address=probe.address();
    probe.close(()=>resolve(address.port));
  });
});
const base=`http://127.0.0.1:${port}`;
let serverError='',server;
try{
  server=spawn(php,['-d','upload_max_filesize=5M','-d','post_max_size=32M','-d','display_errors=0','-S',`127.0.0.1:${port}`,'-t','public'],{
    cwd:workspace,env:environment,stdio:['ignore','ignore','pipe'],windowsHide:true
  });
  server.on('error',error=>{serverError=error.message;});
  server.stderr.on('data',chunk=>{serverError=(serverError+String(chunk)).slice(-4000);});
  let ready=false;
  for(let attempt=0;attempt<50;attempt++){
    if(server.exitCode!==null)break;
    try{const response=await fetch(base+'/?p=login',{signal:AbortSignal.timeout(500)});if(response.ok){ready=true;break;}}catch{/* Wait briefly for PHP startup. */}
    await new Promise(resolve=>setTimeout(resolve,100));
  }
  if(!ready)throw new Error('Test server could not start. '+serverError);
  console.log(`Running checks on ${base}`);
  const result=await new Promise((resolve,reject)=>{
    const tests=spawn(process.execPath,[path.join(root,'tests','http-smoke.mjs')],{
      cwd:root,env:{...environment,SIKHAYA_TEST_URL:base,SIKHAYA_TEST_DB:testDatabase},stdio:'inherit',windowsHide:true
    });
    tests.once('error',reject);
    tests.once('exit',code=>resolve(code??1));
  });
  process.exitCode=result;
}catch(error){console.error(error.message);process.exitCode=1;}
finally{
  if(server&&server.exitCode===null){
    const stopped=new Promise(resolve=>server.once('exit',resolve));
    server.kill();
    await Promise.race([stopped,new Promise(resolve=>setTimeout(resolve,3000))]);
  }
  console.log('Test files retained at '+path.relative(root,workspace));
}
